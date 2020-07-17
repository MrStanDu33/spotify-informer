<?php

namespace App\Http\Controllers;

use \SpotifyWebApi;

class SpotifyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        require_once __DIR__.'/../../../vendor/autoload.php';

        $this->session = new \SpotifyWebAPI\Session(
            'f7520ec8ce3b4525b39217b11846be22',
            '5fea2a40aa7a41619ed6c99b9af57688',
            'http://spotify-informer.localhost/api'
        );

        $options = [
            'scope' => [
                'user-read-playback-state',
            ],
            'auto_refresh' => true,
        ];

        $this->session->setRefreshToken(env('SPOTIFY_REFRESH_TOKEN'));

        if ($this->session->getAccessToken()) {
            $this->session->setAccessToken($accessToken);
            $this->session->setRefreshToken($refreshToken);
        } else {
            $this->session->refreshAccessToken($this->session->getRefreshToken());
        }

        $this->api = new \SpotifyWebAPI\SpotifyWebAPI($options, $this->session);
        $this->api->setSession($this->session);
    }

    public function getPlayingNow() {
        $song = $this->api->getMyCurrentTrack();

        $im = @imagecreate(2048, 512) or die("Cannot Initialize new GD image stream");
        $background_color = imagecolorallocate($im, 255, 255, 255);
        imagecolorallocate( $im, 0, 0, 0 );

        $this->imageroundedrectangle($im, 0, 0, 2047, 511, 10, imagecolorallocate($im, 221, 221, 221));

        if (!$song) {
            imagettftext($im, 72, 0, 160, 160, imagecolorallocate($im, 0, 0, 0), '../storage/app/arial.ttf', 'aucune écoute en cours ...');
        } else {
            imagettftext($im, 72, 0, 512, 160, imagecolorallocate($im, 0, 0, 0), '../storage/app/arial.ttf', (strlen($song->item->name) >= 32) ? substr($song->item->name, 0, 32).'...' : $song->item->name);
            imagettftext($im, 72, 0, 512, 296, imagecolorallocate($im, 0, 0, 0), '../storage/app/arial.ttf', (strlen($song->item->album->name) >= 32) ? substr($song->item->album->name, 0, 32).'...' : $song->item->album->name);
            imagettftext($im, 72, 0, 512, 440, imagecolorallocate($im, 0, 0, 0), '../storage/app/arial.ttf', (strlen($song->item->album->artists[0]->name) >= 32) ? substr($song->item->album->artists[0]->name, 0, 32).'...' : $song->item->album->artists[0]->name);

            $cover = file_get_contents($song->item->album->images[0]->url);
            file_put_contents(__DIR__.'/../../../storage/app/cover.jpg', $cover);
            $coverData = imagecreatefromjpeg(__DIR__.'/../../../storage/app/cover.jpg');
            imagecopy($im, $coverData, 72, 72, 0, 0, 368, 368);
            imagecolorallocate( $im, 0, 0, 0 );
        }
        header("Content-Type: image/png");
        imagepng($im);
        imagedestroy( $im );
    }

    private function imageroundedrectangle(&$img, $x1, $y1, $x2, $y2, $r, $color)
    {
        $r = min($r, floor(min(($x2-$x1)/2, ($y2-$y1)/2)));

        // top border
        imageline($img, $x1+$r, $y1, $x2-$r, $y1, $color);
        // right border
        imageline($img, $x2, $y1+$r, $x2, $y2-$r, $color);
        // bottom border
        imageline($img, $x1+$r, $y2, $x2-$r, $y2, $color);
        // left border
        imageline($img, $x1, $y1+$r, $x1, $y2-$r, $color);

        // top-left arc
        imagearc($img, $x1+$r, $y1+$r, $r*2, $r*2, 180, 270, $color);
        // top-right arc
        imagearc($img, $x2-$r, $y1+$r, $r*2, $r*2, 270, 0, $color);
        // bottom-right arc
        imagearc($img, $x2-$r, $y2-$r, $r*2, $r*2, 0, 90, $color);
        // bottom-left arc
        imagearc($img, $x1+$r, $y2-$r, $r*2, $r*2, 90, 180, $color);

        return true;
    }
}
