<?php

require_once "InstagramPost.php";

class InstalooterToWordPress
{
    const jpg = 'jpg';
    const json = 'json';
    const mp4 = 'mp4';
    const INSTAGRAM_POST = 'INSTAGRAMPOST';
    /* @var string */
    private $dumpFolder;
    private $exportFolder;
    private $posts = [];
    private $years = [];
    private $url_for_images;

    /**
     * @return mixed
     */
    public function getUrlForImages()
    {
        return $this->url_for_images;
    }

    /**
     * @param mixed $url_for_images
     */
    public function setUrlForImages($url_for_images)
    {
        $this->url_for_images = $url_for_images;
    }

    /**
     * @return mixed
     */
    public function getDumpFolder()
    {
        return $this->dumpFolder;
    }

    /**
     * @param mixed $dumpFolder
     */
    public function setDumpFolder($dumpFolder)
    {
        $this->dumpFolder = $dumpFolder;
    }

    public function saveJpg($key, $filename)
    {
        $this->posts[$key][self::jpg] = $this->getDumpFolder() . '/' . $filename;

    }

    public function saveJSON($key, $json)
    {
        $post = new InstagramPost($json);
        $this->posts[$key][self::json] = json_decode($json, true);
        $this->posts[$key][self::INSTAGRAM_POST] = $post;
        $this->addYear($post->getYear());

    }

    // TODO not handling video at this time sorry yo
    public function saveMp4($key, $filename)
    {
        $this->posts[$key][self::mp4] = $this->getDumpFolder() . '/' . $filename;
    }

    public function dumpData()
    {
        print_r($this->posts);
    }

    public function dumpTypeNames() {
        foreach($this->posts as $key => $data) {
//            print_r($data);
            if (array_key_exists(self::json, $data)) {
                echo $data[self::json]['__typename'];
            }
        }
    }
    public function dumpTitles() {
        foreach($this->posts as $key => $data) {
            if (array_key_exists(self::INSTAGRAM_POST, $data)) {
                /* @var InstagramPost */
                $Post = $data[self::INSTAGRAM_POST];
                echo $Post->getTitle();
                echo "\n";
                echo $Post->getTags();
                echo "\n";
                echo $Post->getDate();
                echo "\n";
                echo $Post->getInstagramUrl();
                echo "\n";
            }
        }
    }

    public function printWordPressXml() {

//        echo $this->getWordPressXml();
        echo json_encode($this->years);
    }

    public function generateWordPressXml() {
        foreach ($this->getYears() as $year) {
            echo "generating xml for {$year}\n";
            $xml = $this->getWordPressXml($year);
            $handle = fopen($this->getOutputFolder() . '/' . $year . '.xml', 'w');
            fwrite($handle, $xml);
            fclose($handle);
        }
    }

    public function getWordPressXml($year) {

        $pubDate = date(DATE_RFC822);
        $out = '';
$out .= <<<END
<?xml version="1.0" encoding="UTF-8" ?>
<rss version="2.0"
    xmlns:excerpt="http://wordpress.org/export/1.2/excerpt/"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:wfw="http://wellformedweb.org/CommentAPI/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:wp="http://wordpress.org/export/1.2/"
>

<channel>
    <title>instalooter-to-wordpress</title>
    <link>http://instalooter-to-wordpress</link>
    <description>Another Instalooter To WordPress Export</description>
    <pubDate>{$pubDate}</pubDate>
    <language>en-US</language>
    <wp:wxr_version>1.2</wp:wxr_version>
    <wp:base_site_url>http://instalooter-to-wordpress</wp:base_site_url>
    <wp:base_blog_url>http://instalooter-to-wordpress</wp:base_blog_url>

    <wp:author><wp:author_id>1</wp:author_id><wp:author_login>instalootertowordpresslocaladmin</wp:author_login><wp:author_email>nobody@example.com</wp:author_email><wp:author_display_name><![CDATA[instalootertowordpresslocaladmin]]></wp:author_display_name><wp:author_first_name><![CDATA[]]></wp:author_first_name><wp:author_last_name><![CDATA[]]></wp:author_last_name></wp:author>


    <generator>http://InstalooterToWordpress</generator>
END;

$post_id = 1;

foreach ($this->posts as $key => $data) {

    /* @var $obj InstagramPost */
    $obj = $data[self::INSTAGRAM_POST];
    // TODO isVideo doesnt work yet
    if ($obj && !$obj->isVideo() && $obj->getDate('Y') == $year && array_key_exists(self::jpg, $data)) {
        $post_id++;
        // TODO note that post_name gets turned into the slug, not sure necessary
        $post_name = 'instagram-id-' . $obj->getId();
        // TODO what format is pubDate supposed to be?
        $pubDate = $obj->getDate(); // TODO format?
        $instagramUrl = $obj->getInstagramUrl();
        $img_url = $this->getUrlForImages() . $data[self::jpg];
        $width = $obj->getWidth();
        $height = $obj->getHeight();
        $post_content = "<img src=\"{$img_url}\" alt=\"\" width=\"{$width}\" height=\"$height\" class=\"aligncenter instalooter-to-wordpress\" /></a> from Instagram <a href=\"{$instagramUrl}\">{$instagramUrl}</a> via <span class=\"InstalooterToWordPress\">InstalooterToWordPress</a>";
        // TODO probably a more correct way to do this
        $post_date = $obj->getDate('Y-m-d H:i:s');
        $post_date_gmt = $obj->getDate('Y-m-d H:i:s');
        $title = htmlentities($obj->getTitle(), ENT_XML1);
        $out .= <<<END
    <item>
        <title>{$title}</title>
        <link>{$instagramUrl}</link>
        <pubDate>{$pubDate}</pubDate>
        <dc:creator><![CDATA[wordpresslocaladmin]]></dc:creator>
        <guid isPermaLink="false">{$instagramUrl}</guid>
		<wp:post_id>{$post_id}</wp:post_id>
        <description></description>
        <content:encoded><![CDATA[{$post_content}]]></content:encoded>
        <excerpt:encoded><![CDATA[]]></excerpt:encoded>
        <wp:post_date>{$post_date}</wp:post_date>
        <wp:post_date_gmt>{$post_date_gmt}</wp:post_date_gmt>
        <wp:comment_status>open</wp:comment_status>
        <wp:ping_status>open</wp:ping_status>
        <wp:post_name>{$post_name}</wp:post_name>
        <wp:status>publish</wp:status>
        <wp:post_parent>0</wp:post_parent>
        <wp:menu_order>0</wp:menu_order>
        <wp:post_type>post</wp:post_type>
        <wp:post_password></wp:post_password>
        <wp:is_sticky>0</wp:is_sticky>
        <category domain="category" nicename="instalooter-import"><![CDATA[instalooter-import]]></category>
END;

        $tags = explode(',', $obj->getTags());
        foreach ($tags as $tag) {
            $out .= "<category domain=\"post_tag\" nicename=\"{$tag}\"><![CDATA[$tag]]></category>\n";
        }
        $out .= <<<END

    </item>

END;
    }


}


        $out .= <<<END
</channel>
</rss>
END;
return $out;
    }

    private function addYear($year)
    {
        if (!in_array($year, $this->years)) {
            $this->years[] = $year;
        }
    }

    public function setWordPressExportFolder($string)
    {
        $this->exportFolder = $string;
    }

    /**
     * @throws Exception
     */
    public function readInstalooterFolder()
    {
        if ($handle = opendir($this->getDumpFolder())) {
            while (false !== ($filename = readdir($handle))) {
                list($key, $extension) = explode('.', $filename);

                switch($extension) {
                    case InstalooterToWordPress::jpg:
                        echo 'Reading JPEG ' . $filename . "\n";
                        $this->saveJpg($key, $filename);
                        break;
                    case InstalooterToWordPress::json:
                        echo 'Reading JSON ' . $filename . "\n";
                        $this->saveJSON($key, file_get_contents($this->getDumpFolder() . '/' . $filename));
                        break;
                    // TODO handle video files
                    case InstalooterToWordPress::mp4:
                        echo 'Ignoring video ' . $filename . "\n";
                        $this->saveMp4($key, $filename);
                        break;
                    case 'gitignore':
                    case '':
                        break;
                    default:
                        throw new Exception($filename . ' in the input folder not sure how to handle.');
                }
            }
            closedir($handle);
        }
    }

    /**
     * @param $year
     * @return array
     */
    private function getYears() {
        sort($this->years);
        return $this->years;
    }

    private function getOutputFolder()
    {
        return $this->exportFolder;
    }

    public function run()
    {
        $this->readInstalooterFolder();
        $this->generateWordPressXml();
    }


}