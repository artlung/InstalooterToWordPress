<?php

require_once "InstagramPost.php";

class InstalooterToWordPress
{
    const jpg = 'jpg';
    const json = 'json';
    const mp4 = 'mp4';
    const INSTAGRAMPOST = 'INSTAGRAMPOST';
    /* @var string */
    public $dumpFolder;
    private $posts = [];

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
        $this->posts[$key][self::json] = json_decode($json, true);
        $this->posts[$key][self::INSTAGRAMPOST] = new InstagramPost($json);
    }

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
            if (array_key_exists(self::INSTAGRAMPOST, $data)) {
                /* @var InstagramPost */
                $Post = $data[self::INSTAGRAMPOST];
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
        echo $this->getWordPressXml();
    }


    public function getWordPressXml() {

        $pubDate = date(DATE_RFC822);
        $out = '';
$out .= <<<END
<?xml version="1.0" encoding="UTF-8" ?>
<!-- generator="WordPress/3.9" created="2014-04-25 14:19" -->
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
    $obj = $data[self::INSTAGRAMPOST];
    if ($obj && !$obj->isVideo() && $obj->getDate('Ym') == 201311 && $obj->getDate('Ymd') <= 20131107) {
        $post_id++;
        $post_name = 'instagram-id-' . $obj->getId();
        $pubDate = $obj->getDate(); // TODO format?
        $instagramUrl = $obj->getInstagramUrl();
        $img_url = 'http://joecrawford.com/' . $data[self::jpg];
        $width = $obj->getWidth();
        $height = $obj->getHeight();
        $post_content = "<img src=\"{$img_url}\" alt=\"\" width=\"{$width}\" height=\"$height\" class=\"aligncenter instalooter-to-wordpress\" /></a> from Instagram <a href=\"{$instagramUrl}\">{$instagramUrl}</a> via <span class=\"InstalooterToWordPress\">InstalooterToWordPress</a>";
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
<!--        <wp:postmeta>-->
<!--            <wp:meta_key>_thumbnail_id</wp:meta_key>-->
<!--            <wp:meta_value><![CDATA[6]]></wp:meta_value>-->
<!--        </wp:postmeta>-->
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


}