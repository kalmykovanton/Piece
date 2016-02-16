# Piece
Simple template engine component for ITCourses Jazz PHP framework
## How to use
First of all, create an array of settings for Piece component:
```php
// settings array
$settings = [
    // folder, where you store your view's
    'viewsFolder' => __DIR__ . '/views',
    // file extension, which you use for your
    // template and views files
    'fileExtension' => '.phtml'
];
```
After that, create an instance of Piece component and pass into it your settings array:
```php
use Piece\ViewEngine;
$view = new ViewEngine($settings);
```
Now, you can run Piece's render() method, which it first argument is name of view and second - array of view's parameters (if any):
```php
$view->render('home', ['content'=>'Some content for home page.']);
```
Supposing our view's files stored in *Views* folder and template's files in *Views/templates* folder.

**Views/home.phtml** view example:
```html
@template('templates/template');

<h1>This is view content.</h1>
<p> <?=$content?></p>
```
In this case, **@template('templates/template');** flag tell Piece's render() method where it can find template for this view.

**Views/templates/template.phtml** template example:
```html
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Home page</title>
</head>
<body>
    @embed;
</body>
</html>
```
**@embed;** flag tell Piece's render() method where it must inject view's body.

You can use any PHP construction and variables, in view's files and template's files. All of they are processed by render() method.

This method also provides XSS protection.
