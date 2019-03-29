#PHP Obfuscator

Free, Open Source, Published under the MIT License.

This tool parses php with the best existing php parser [PHP-Parser 1.x](https://github.com/nikic/PHP-Parser/tree/1.x),
which is an awesome php parsing library written by [nikic](https://github.com/nikic).


## Example usage
```php
$obfuscator = new pmaslak\Obfuscator([
    'files_target' => 'obfuscated_file.php',
    'obfuscation_options' => ['no-obfuscate-variable-name', 'no-obfuscate-method-name', 'no-obfuscate-class-name', 'no-obfuscate-property-name']
]);
$obfuscator->obfuscateFile('file_to_obfuscate.php');

```


## Credits
- [pk-fr](https://github.com/pk-fr) Thanks for obfuscation core!
- [PHP-Parser 1.x](https://github.com/nikic/PHP-Parser/tree/1.x)
- [nikic](https://github.com/nikic)


## License
MIT license 
