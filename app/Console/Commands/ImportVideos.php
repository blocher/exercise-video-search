<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Alaouy\Youtube\Facades\Youtube;
use App\Video;
use App\Tag;

class ImportVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:videos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import videos';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Convert ISO 8601 values like PT15M33S
     * to a total value of seconds.
     *
     * @param string $ISO8601
     */
    function durationToSeconds($ISO8601)
    {
        preg_match('/\d{1,2}[H]/', $ISO8601, $hours);
        preg_match('/\d{1,2}[M]/', $ISO8601, $minutes);
        preg_match('/\d{1,2}[S]/', $ISO8601, $seconds);

        $duration = [
            'hours'   => $hours ? $hours[0] : 0,
            'minutes' => $minutes ? $minutes[0] : 0,
            'seconds' => $seconds ? $seconds[0] : 0,
        ];

        $hours   = substr($duration['hours'], 0, -1);
        $minutes = substr($duration['minutes'], 0, -1);
        $seconds = substr($duration['seconds'], 0, -1);

        $toltalSeconds = ($hours * 60 * 60) + ($minutes * 60) + $seconds;

        return $toltalSeconds;
    }

    public function importYouTubeItems($playlist_id, $page=null) {
        $items = Youtube::getPlaylistItemsByPlaylistId($playlist_id, $page);

        $videos = [];
        foreach ($items['results'] as $item) {
            $video = Video::firstOrNew(['youtube_id'=>$item->id]);
            $video->etag = $item->etag;
            $video->video_id = $item->contentDetails->videoId;
            $date = str_replace('T',' ', substr($item->snippet->publishedAt,0,16) );

            $video->published_at = new \DateTime($date, new \DateTimeZone('America/New_York'));
            $video->title = $item->snippet->title;
            $video->description = $item->snippet->description;
            $video->position = $item->snippet->position;
            $video->save();
            $videos[] =$video->video_id;
            $this->info($video->title . ' SAVED');
        }

        $videos = Youtube::getVideoInfo($videos);

        foreach ($videos as $item) {
            $video = Video::firstOrNew(['video_id'=>$item->id]);
            $video->duration = $this->durationToSeconds($item->contentDetails->duration);
            $video->view_count = $item->statistics->viewCount;
            $video->like_count = $item->statistics->likeCount;
            $video->dislike_count = $item->statistics->dislikeCount;
            $video->favorite_count = $item->statistics->favoriteCount;
            $video->comment_count = $item->statistics->commentCount;
            $video->save();

            if (isset($item->snippet->tags)) {
                $tags = $item->snippet->tags;
                foreach ($tags as $name) {
                    $tag = Tag::firstOrCreate(['name'=>$name]);
                    $video->tags()->syncWithoutDetaching([$tag->id]);
                }
            }




            $this->info($item->snippet->title . ' ADDITIONAL INFO ADDED');
        }

        if ($items['info']['nextPageToken']) {
            $this->importYouTubeItems($playlist_id,$items['info']['nextPageToken']);
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $channel = Youtube::getChannelByName('ChristineSalus');
        $playlist_id = $channel->contentDetails->relatedPlaylists->uploads;
        $playlist = Youtube::getPlaylistById($playlist_id);
        $this->importYouTubeItems($playlist_id);

    }
}
