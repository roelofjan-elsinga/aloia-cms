# Flat File CMS

This package contains a drop-in CMS that uses files to store its contents.

## Installation

You can include this package through Composer using:

```bash
composer require roelofjan-elsinga/flat-file-cms
```

and if you want to customize the folder structure, then publish the configuration through:

```bash
php artisan vendor:publish --provider="FlatFileCms\\FlatFileCmsServiceProvider"
```

## Usage of Article

To load all articles located at the folder you specified in 
``config('flatfilecms.articles.folder_path')`` you can use the following script:

```php
use FlatFileCms\Article;

/**@var Article[]*/
$articles = Article::all();
```

You can use that to display your posts on a page. You can also load a single post, using:

```php
$article = Article::forSlug($post_slug);
```

If you only want all published posts, you'll need to retrieve them like so:

```php
$published_articles = Article::published();
```

To get the contents of the articles.json file, you can run:

```php
$articles = Article::raw();
```

And finally, to update the data in ```articles.json```, you can run:

```php
$posts = Article::raw();

$posts->push([
    'filename' => 'my-beautiful-post.md', // or: .html, .txt
    'description' => 'This post is about beautiful things',
    'postDate' => date('Y-m-d'),
    'isPublished' => true,
    'isScheduled' => false,
]);

Article::update();
```

## Content blocks

You can also manage small content blocks for your website through this package. 
Start by specifying the folder path of your content blocks in 
``config('flatfilecms.content_blocks.data_folder')``.

You'll need to register the facade into your application, by placing the following 
line to your aliases in ``config/app.php``:

```php
'Block' => \FlatFileCms\Facades\BlockFacade::class,
```

Now you can use the facade in your views by using:

```php
{!! Block::get('content-file-name') !!}
```

If you're using content files managed through the CMS, you can also include content blocks in 
HTML and Markdown files. You can add several different attributes to enhance these content blocks:

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

<!==resulting HTML output==>
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

<!==resulting HTML output==>
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

<!==resulting HTML output==>
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

<!==resulting HTML output==>
<div class="primary-block">
    <a href="/link-to-a-page">
        <h1>This is a title</h1>
    </a>
</div>
```

This facade will look for a file in the folder you specified in 
``config('flatfilecms.content_blocks.data_folder')``. 
The Facade will parse the contents of the file to HTML to be able to render it. 
If no file could be found, an empty string will be returned.

NOTE: You should not specify the extension of the filename you're passing to ``Block::get()``.
This will be parsed automatically.

## Testing

You can run the included tests by running ``./vendor/bin/phpunit`` in your terminal.