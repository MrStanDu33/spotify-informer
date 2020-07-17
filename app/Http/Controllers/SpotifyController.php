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
        header('Cache-control: private, max-age=0, no-cache');
    }

    public function getPlayingNow() {
        $song = $this->api->getMyCurrentTrack();
        $im = @imagecreatetruecolor(2048, 512) or die("Cannot Initialize new GD image stream");
        imagesavealpha($im, true);
        $background_color = imagecolorallocate($im, 255, 255, 255);
        $trans_colour = imagecolorallocatealpha($im, 0, 0, 0, 127);
        imagefill($im, 0, 0, $trans_colour);

        $this->imageroundedrectangle($im, 0, 0, 2047, 511, 20, imagecolorallocate($im, 221, 221, 221));

        $this->imageroundedrectangle($im, 3, 3, 2043, 507, 20, imagecolorallocate($im, 255, 255, 255));

        if (!$song) {
            imagettftext($im, 72, 0, 160, 160, imagecolorallocate($im, 0, 0, 0), '../storage/app/arial.ttf', 'aucune écoute en cours ...');
            imagecolorallocate( $im, 0, 0, 0 );
        } else {
            imagettftext($im, 72, 0, 512, 160, imagecolorallocate($im, 246, 150, 115), '../storage/app/arial.ttf', (strlen($song->item->name) >= 32) ? substr($song->item->name, 0, 32).'...' : $song->item->name);
            imagecolorallocate( $im, 0, 0, 0 );
            imagettftext($im, 50, 0, 512, 347, imagecolorallocate($im, 0, 0, 0), '../storage/app/arial.ttf', (strlen($song->item->album->name) >= 32) ? substr($song->item->album->name, 0, 32).'...' : $song->item->album->name);
            imagecolorallocate( $im, 0, 0, 0 );
            imagettftext($im, 72, 0, 512, 440, imagecolorallocate($im, 0, 0, 0), '../storage/app/arial.ttf', (strlen($song->item->album->artists[0]->name) >= 32) ? substr($song->item->album->artists[0]->name, 0, 32).'...' : $song->item->album->artists[0]->name);
            imagecolorallocate( $im, 0, 0, 0 );

            $cover = file_get_contents($song->item->album->images[0]->url);
            file_put_contents(__DIR__.'/../../../storage/app/cover.jpg', $cover);
            $coverData = imagecreatefromjpeg(__DIR__.'/../../../storage/app/cover.jpg');
            imagecopyresampled($im, $coverData, 72, 72, 0, 0, 368, 368, imagesx($coverData),imagesy($coverData));
            imagecolorallocate( $im, 0, 0, 0 );
        }
        header("Content-Type: image/png");
        imagepng($im);
        imagedestroy( $im );
    }

    private function imageroundedrectangle(&$img,$x,$y,$cx,$cy,$rad,$col)
    {




        imagefilledrectangle($img,$x,$y+$rad,$cx,$cy-$rad,$col);
        imagefilledrectangle($img,$x+$rad,$y,$cx-$rad,$cy,$col);

        $dia = $rad*2;

        // Now fill in the rounded corners

        imagefilledellipse($img, $x+$rad, $y+$rad, $rad*2, $dia, $col);
        imagefilledellipse($img, $x+$rad, $cy-$rad, $rad*2, $dia, $col);
        imagefilledellipse($img, $cx-$rad, $cy-$rad, $rad*2, $dia, $col);
        imagefilledellipse($img, $cx-$rad, $y+$rad, $rad*2, $dia, $col);

        return true;
    }
}
