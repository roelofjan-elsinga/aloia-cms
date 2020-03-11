# Aloia CMS

[![Build status](https://travis-ci.com/roelofjan-elsinga/flat-file-cms.svg)](https://travis-ci.com/roelofjan-elsinga/aloia-cms)
[![StyleCI Status](https://github.styleci.io/repos/192778142/shield)](https://github.styleci.io/repos/192778142)
[![Code Coverage](https://codecov.io/gh/roelofjan-elsinga/aloia-cms/branch/master/graph/badge.svg)](https://codecov.io/gh/roelofjan-elsinga/aloia-cms)
[![Scrutinizer](https://scrutinizer-ci.com/g/roelofjan-elsinga/aloia-cms/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/roelofjan-elsinga/aloia-cms/)
[![Total Downloads](https://poser.pugx.org/roelofjan-elsinga/aloia-cms/downloads)](https://packagist.org/packages/roelofjan-elsinga/aloia-cms)
[![Latest Stable Version](https://poser.pugx.org/roelofjan-elsinga/aloia-cms/v/stable)](https://packagist.org/packages/roelofjan-elsinga/aloia-cms)
[![License](https://poser.pugx.org/roelofjan-elsinga/aloia-cms/license)](https://packagist.org/packages/roelofjan-elsinga/aloia-cms)

This package contains a drop-in CMS that uses files to store its contents.

For the full documentation, go to the [Aloia CMS Documentation](https://aloiacms.com) website.

## Installation

You can include this package through Composer using:

```bash
composer require roelofjan-elsinga/aloia-cms
```

and if you want to customize the folder structure, then publish the configuration through:

```bash
php artisan vendor:publish --provider="AloiaCms\\AloiaCmsServiceProvider"
```

## Creating a custom content type

Creating a custom content type is very simple. All you have to do is create a class that extends ``AloiaCms\Models\Model``, 
specify a ``$folder_path``, and optionally add required fields to ``$required_fields``. An example can be found below:

```php
namespace App\Models;

use AloiaCms\Models\Model;

class PortfolioItem extends Model
{
    protected $folder = 'portfolio_items';

    protected $required_fields = [
        'name',
        'github_url',
        'description',
    ];
}
```

Once you have a class like this, you can interact with it like described under "Usage of models"

## Built-in models

There are 4 built-in models which you can use without any additional set-up:
- Page
- Article
- ContentBlock
- MetaTag

Of course you can add your own models as described at "Creating a custom content type".

## Usage of models

In this example we're looking at one of the built-in content types: Article. 
You can use these same steps for all classes that extend from "AloiaCms\Models\Model".


To load all articles in the "articles" folder in the folder you specified in 
``config('aloiacms.collection_path')`` you can use the following script:

```php
use AloiaCms\Models\Article;

/**@var Article[]*/
$articles = Article::all();
```

You can use that to display your posts on a page. You can also load a single post, using:

```php
$post_slug = 'this-post-is-amazing';

$article = Article::find($post_slug);
```

If you only want all published posts, you'll need to retrieve them like so:

```php
$published_articles = Article::published();
```

To get the raw contents of each article (content + front matter), you can use:

```php
$post_slug = 'this-post-is-amazing';

$articles = Article::find($post_slug)->rawContent();
```

And finally, to update your article, you can run:

```php
use Carbon\Carbon;

$post_slug = 'this-post-is-amazing';

Article::find($post_slug)
    ->setExtension('md') // md is the default, but you can use html as well.
    ->setMatter([
        'description' => 'This post is about beautiful things',
        'is_published' => true,
        'is_sechduled' => false,
        // Either use post_date in setMatter() or setPostDate()
        'post_date' => date('Y-m-d')
    ])
    ->setPostDate(Carbon::now())
    ->setBody('# This is the content of an article')
    ->save();
```

## Content blocks

You can manage small content blocks for your website through this package.

The content of the blocks are stored in a folder called "content_blocks" 
inside of the ``config('aloiacms.collections_path')`` folder.

You'll need to register the facade into your application, by placing the following 
line to your aliases in ``config/app.php``:

```php
'Block' => \AloiaCms\Facades\BlockFacade::class,
```

Now you can use the facade in your blade views by using:

```php
{!! Block::get('content-file-name') !!}
```

You can also include content blocks in your HTML and Markdown files. You can add several different attributes 
to enhance these content blocks:

- class
- id
- style
- href

The class, id, and style will be applied to a wrapping div around the content in the block.
The link in the href attribute will be applied to a wrapping anchor tag inside the wrapping div.

Examples:

Using a content block in a HTML or Markdown file managed through the CMS:

```html
<!--content.html-->
<h1>This is a title</h1>

<!--page managed through the CMS-->
===content===

<!--resulting HTML output-->
<div>
    <h1>This is a title</h1>
</div>
```

Adding a class to the content block:

```html
<!--content.html-->
<h1>This is a title</h1>

<!--page managed through the CMS-->
===content[class=primary-block]===

<!--resulting HTML output-->
<div class="primary-block">
    <h1>This is a title</h1>
</div>
```

Adding multiple classes and a style tag to the content block:

```html
<!--content.html-->
<h1>This is a title</h1>

<!--page managed through the CMS-->
===content[class=block primary-block,style=color:red;]===

<!--resulting HTML output-->
<div class="block primary-block" style="color:red;">
    <h1>This is a title</h1>
</div>
```

When you add a href attribute to the content block, you get a nested anchor tag:
```html
<!--content.html-->
<h1>This is a title</h1>

<!--page managed through the CMS-->
===content[class=primary-block,href=/link-to-a-page]===

<!--resulting HTML output-->
<div class="primary-block">
    <a href="/link-to-a-page">
        <h1>This is a title</h1>
    </a>
</div>
```

This facade will look for a file in the folder you specified in 
``config('aloiacms.collections_path')``. 
The Facade will parse the contents of the file to HTML to be able to render it. 
If no file could be found, an empty string will be returned.

NOTE: You should not specify the extension of the filename you're passing to ``Block::get()``.
This will be parsed automatically.

## Testing

You can run the included tests by running ``./vendor/bin/phpunit`` in your terminal.
