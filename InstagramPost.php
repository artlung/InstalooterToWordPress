<?php


class InstagramPost
{
    private $jsonstring;

    const instagram_base = 'https://www.instagram.com/p/';
    function __construct($json) {
        $this->jsonstring = $json;
    }

    private function parsed() {
        return json_decode($this->jsonstring, true);
    }

    public function getTitle() {
        $text = $this->parsed()['edge_media_to_caption']['edges'][0]['node']['text'];
        return $text ?: 'Untitled ' . $this->getShortcode();
    }

    public function getTags() {
        $tags = [];
        $parts = explode(' ', $this->getTitle());
        foreach ($parts as $candidate) {
            $step2 = '';
            switch ((substr($candidate, 0, 1))) {
                case '#':
                case '@':
                    $step2 = substr($candidate, 1);
                    break;
            }
            if ($step2) {
                $tags[] = $step2;
            }
        }
        return implode(',', $tags);
    }

    public function getLikeCount() {
        return $this->parsed()['edge_media_preview_like']['count'];
    }

    public function getShortcode() {
        return $this->parsed()['shortcode'];
    }

    public function getInstagramUrl() {
        return self::instagram_base . $this->getShortcode() . '/';
    }

    public function getTakenAtTimeStamp() {
        return '@' . $this->parsed()['taken_at_timestamp'];
    }

    public function isVideo() {
        return $this->parsed()['is_video'] === '1';
    }
    public function getHeight() {
        return $this->parsed()['height'];
    }
    public function getWidth() {
        return $this->parsed()['width'];
    }
    public function getId() {
        return $this->parsed()['id'];
    }


    public function getDate($format = false) {
        $date = new DateTime($this->getTakenAtTimeStamp());
        if ($format) {
            return $date->format($format);
        } else {
            return $date->format(DATE_ATOM);
        }
    }





}