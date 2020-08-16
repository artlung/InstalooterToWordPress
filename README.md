# InstalooterToWordPress

## Background
The itch I needed to scratch was this: I had posts from many years ago that I had not imported to my personal website. I started using [IFTTT](https://ifttt.com) in 2013 to import them as I posted them and that has worked okay since then.
[Instalooter](https://github.com/althonos/InstaLooter) is an excellent tool for downloading images and metadata for your instagram posts. 

## Requirements
You will need to be able to run a recent version of PHP on the command line. You'll need Instalooter running locally.
data and run something like ``

## Steps on the Command Line!

1. `cd instalooter_dumps`
1. Typical usage would be to download a particular user's data, in this case I'll use my own little used `bloggingbot` account as an example, use your own here. Note that I have not tested this against multiple-image posts or videos yet! But here's the command I used: \
\
`instalooter user bloggingbot --dump-json` \
  \
  *Output:* \
   2020-08-16 14:33:33 MacbookLungPro.local instalooter.cli[37084] NOTICE Starting download of bloggingbot \
   2020-08-16 14:33:34 MacbookLungPro.local instalooter.cli[37084] SUCCESS Downloaded 1 post.

1. `cd ..`
1. Edit `run.php` and edit the line that says `$I2W->setBaseUrlForAssets` and change the value to match where you are going to put these files. I use the WordPress plugin [Import External Images 2](https://github.com/VR51/import-external-images-2) after import to import them into my WordPress instance. You may have another way.
1. `php run.php`
    *Output:* \
    \
    Reading JPEG 1722297278800439303.jpg \
    Reading JSON 1722297278800439303.json \
    generating xml for 2018

1. You should now have a file called `2008.xml` in the `wordpress_imports` directory you can import into WordPress.

---

Questions? Feel free to comment here or hit me on email [joe@artlung.com](mailto:joe@artlung.com) or twitter [@artlung](https://twitter.com/artlung)


