<?php

require_once "InstagramPost.php";

class InstalooterToWordPress
{
    const jpg = 'jpg';
    const json = 'json';
    const mp4 = 'mp4';
    const INSTAGRAM_POST = 'INSTAGRAMPOST';
    /* @var string */
    private $dumpFolder = 'instalooter_dumps';
    private $exportFolder = 'wordpress_imports';
    private $posts = [];
    private $years = [];
    private $base_url_for_assets;
    /**
     * @var array
     */
    private $categories = [];
    /**
     * @var array
     */
    private $tags = [];
    private $author_email = "nobody@example.org";
    private $author_display_name = "Default Author Name";

    /**
     * @return mixed
     */
    public function getBaseUrlForImages()
    {
        return $this->base_url_for_assets;
    }

    /**
     * @param mixed $base_url_for_assets
     */
    public function setBaseUrlForAssets($base_url_for_assets)
    {
        $this->base_url_for_assets = $base_url_for_assets;
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

    /**
     * TODO not handling video at this time sorry yo
     * @param $key
     * @param $filename
     */
    public function saveMp4($key, $filename)
    {
        $this->posts[$key][self::mp4] = $this->getDumpFolder() . '/' . $filename;
    }

    /**
     * Generate the actual WordPress Import files by year
     */
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
        $authorEmail = $this->getAuthorEmail();
        $authorDisplayName = $this->getAuthorDisplayName();

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

    <wp:author><wp:author_id>1</wp:author_id><wp:author_login>instalootertowordpresslocaladmin</wp:author_login><wp:author_email>{$authorEmail}</wp:author_email><wp:author_display_name><![CDATA[{$authorDisplayName}]]></wp:author_display_name><wp:author_first_name><![CDATA[]]></wp:author_first_name><wp:author_last_name><![CDATA[]]></wp:author_last_name></wp:author>


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
        $itemPubDate = $obj->getDate(); // TODO format?
        $instagramUrl = $obj->getInstagramUrl();
        $img_url = $this->getBaseUrlForImages() . $data[self::jpg];
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
        <pubDate>{$itemPubDate}</pubDate>
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

END;

        foreach ($this->getDefaultCategories() as $category) {
            $encoded_category = htmlentities($category, ENT_XML1);
            $out .= "        <category domain=\"category\" nicename=\"{$encoded_category}\"><![CDATA[$encoded_category]]></category>\n";
        }

        $tags = explode(',', $obj->getTags());
        $defaultTags = $this->getDefaultTags();
        foreach (array_merge($tags, $defaultTags) as $tag) {
            if (trim($tag)) {
                $out .= "        <category domain=\"post_tag\" nicename=\"{$tag}\"><![CDATA[$tag]]></category>\n";
            }
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
                    case 'DS_Store':
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
        if (count($this->posts) == 0) {
            echo "No files were found in " . $this->getDumpFolder() . "\n";
        }
    }

    public function addCategory($string)
    {
        $this->categories[] = $string;
    }

    public function addTag($string)
    {
        $this->tags[] = $string;
    }

    private function getDefaultTags()
    {
        return $this->tags;
    }

    private function getDefaultCategories()
    {
        return $this->categories;
    }

    private function getAuthorEmail()
    {
        return $this->author_email;
    }

    private function getAuthorDisplayName()
    {
        return $this->author_display_name;
    }

    /**
     * @param string $author_display_name
     */
    public function setAuthorDisplayName($author_display_name)
    {
        $this->author_display_name = $author_display_name;
    }

    /**
     * @param string $author_email
     */
    public function setAuthorEmail($author_email)
    {
        $this->author_email = $author_email;
    }


}