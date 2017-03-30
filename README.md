# [Magefan](http://magefan.com/) Blog Extension for Magento 2

Blog module allows you to create a full-fledged blog on your [Magento 2](http://magento.com/) Store.

## Features
  * Unlimited blog posts and categories
  * Post tags are available
  * Multi-store support
  * SEO friendly permalinks
  * Post media gallery
  * Facebook, Disqus and Google+ comments on post page
  * Related products and related post
  * Available posts search
  * Lazy load on posts list
  * Author information and posts by author
  * Recent Posts, Archive, Categories, Search From, Tag Cloud sidebar widgets
  * Import Posts and Categories form WordPress and AW Blog extension for M1
  * Recent Posts Widget
  * Sitemap XML
  * Posts and Categories duplicate
  * Blog Rss Feed
  * Open Graph (OG) meta tags
  * REST API  
  * Compatible with [Porto Theme](https://themeforest.net/item/porto-ultimate-responsive-magento-theme/9725864?ref=magefan) for Magento 2
  * Accelerated Mobile Pages (AMP) Project support. To enable AMP view on blog pages [Magento Amp Extension](http://magefan.com/accelerated-mobile-pages/) by Plumrocket is required.

## Simple Demo
http://magefan.com/magento2-blog-extension/#demo

## Online Documentation
http://magefan.com/docs/magento-2-blog/

## Requirements
  * Magento Community Edition 2.1.x or Magento Enterprise Edition 2.1.x

## Installation Method 1 - Installing via composer
  * Open command line
  * Using command "cd" navigate to your magento2 root directory
  * Run command: composer require magefan/module-blog

  

## Installation Method 2 - Installing using archive
  * Download [ZIP Archive](https://github.com/magefan/module-blog/archive/master.zip)
  * Extract files
  * In your Magento 2 root directory create folder app/code/Magefan/Blog
  * Copy files and folders from archive to that folder
  * In command line, using "cd", navigate to your Magento 2 root directory
  * Run commands:
```
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```

## Support
If you have any issues, please [contact us](mailto:support@magefan.com)
then if you still need help, open a bug report in GitHub's
[issue tracker](https://github.com/magefan/module-blog/issues).

Please do not use Magento Connect's Reviews or (especially) the Q&A for support.
There isn't a way for us to reply to reviews and the Q&A moderation is very slow.

## Donate to us
All Magefan extension are absolutely free and licensed under the Open Software License version 3.0. We want to create more awesome features for you and bring up new releases as fast as we can. We hope for your support.
http://magefan.com/donate/

## License
The code is licensed under [Open Software License ("OSL") v. 3.0](http://opensource.org/licenses/osl-3.0.php).
