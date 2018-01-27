# [Magento 2 Blog Extension](https://magefan.com/magento2-blog-extension) by Magefan

[![Total Downloads](https://poser.pugx.org/magefan/module-blog/downloads)](https://packagist.org/packages/magefan/module-blog)
[![Latest Stable Version](https://poser.pugx.org/magefan/module-blog/v/stable)](https://packagist.org/packages/magefan/module-blog)

This Magento 2 Blog module allows you to create a full-fledged blog on your [Magento 2](http://magento.com/) Store.


## Features
  * Unlimited blog posts, multi-level categories, and tags
  * Multilanguage and Multiple Websites Support
  * Facebook, Disqus, Google+, and Magefan Magento Comments
  * Related products and posts
  * Post media gallery & video
  * Next & Previous Post
  * Posts search
  * Posts Lazy Load
  * Author information and posts by author
  * Recent Posts, Featured Posts, Archive, Categories, Search From, Tags Cloud sidebar widgets
  * Import Posts and Categories form WordPress and AW Blog extension for M1
  * Posts and Categories duplication
  * Blog Rss Feed
  * REST API  
  * 100% Open Source
  * Compatible with [Porto Theme](https://themeforest.net/item/porto-ultimate-responsive-magento-theme/9725864?ref=magefan) for Magento 2
  * Accelerated Mobile Pages (AMP) Project support. To enable AMP view on blog pages [Magento Amp Extension](http://magefan.com/accelerated-mobile-pages/) by Plumrocket is required.
  
## SEO Features
  * Blog Sitemap XML
  * SEO-friendly URLs
  * Structured Data
  * Open Graph (OG) meta tags
  * Canonical URL  

## Storefront Demo
http://blog.demo.magefan.com/blog/
## Admin Panel Demo
http://blog.demo.magefan.com/admin/

## Online Documentation
http://magefan.com/docs/magento-2-blog/

## Requirements
  * Magento Community Edition 2.1.x-2.2.x or Magento Enterprise Edition 2.1.x-2.2.x

## Installation Method 1 - Installing via composer
  * Open command line
  * Using command "cd" navigate to your magento2 root directory
  * Run command: composer require magefan/module-blog
  
  * If you are using Magento 2.2.x please run the command: composer require magefan/module-blog-m22

  

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

Please do not use Magento Marketplace Reviews or (especially) the Q&A for support.
There isn't a way for us to reply to reviews and the Q&A moderation is very slow.

## Need More Features?
Please contact us to get a quote
https://magefan.com/contact

## License
The code is licensed under [Open Software License ("OSL") v. 3.0](http://opensource.org/licenses/osl-3.0.php).
