# PHP Obfuscator

Free, Open Source, Published under the MIT License.

This tool is for commercial and non commercial usage. It basically cover all features of [yakpro-po](https://github.com/pk-fr/yakpro-po)
under PHP library available from composer.

## Example usage
```php
use pmaslak\PhpObfuscator\Obfuscator;

$obfuscator = new Obfuscator([
    'allowed_mime_types' => ['text/x-php'],
    'obfuscation_options' => ['no-obfuscate-variable-name', 'no-obfuscate-method-name', 'no-obfuscate-class-name', 'no-obfuscate-property-name']
]);

$obfuscator->obfuscateFile('/dir/example_file.php', '/new_dir/obfuscated_file.php');

$obfuscator->obfuscateDirectory('/dir/to_obfuscate/', '/dir/obfuscated/');

```

## Configuration
    no-strip-indentation              multi line output
    strip-indentation                 single line output
    
    no-shuffle-statements             do not shuffle statements
    shuffle-statements                       shuffle statements
    
    no-obfuscate-string-literal       do not obfuscate string literals
    obfuscate-string-literal                 obfuscate string literals
    
    no-obfuscate-loop-statement       do not obfuscate loop statements
    obfuscate-loop-statement                 obfuscate loop statements
    
    no-obfuscate-if-statement         do not obfuscate if statements
    obfuscate-if-statement                   obfuscate if statements
    
    no-obfuscate-constant-name        do not obfuscate constant names
    obfuscate-constant-name                  obfuscate constant names
    
    no-obfuscate-variable-name        do not obfuscate variable names
    obfuscate-variable-name                  obfuscate variable names
    
    no-obfuscate-function-name        do not obfuscate function names
    obfuscate-function-name                  obfuscate function names
    
    no-obfuscate-class_constant-name  do not obfuscate class constant names
    obfuscate-class_constant-name            obfuscate class constant names
    
    no-obfuscate-class-name           do not obfuscate class names
    obfuscate-class-name                     obfuscate class names
    
    no-obfuscate-interface-name       do not obfuscate interface names
    obfuscate-interface-name                 obfuscate interface names
    
    no-obfuscate-trait-name           do not obfuscate trait names
    obfuscate-trait-name                     obfuscate trait names
    
    no-obfuscate-property-name        do not obfuscate property names
    obfuscate-property-name                  obfuscate property names
    
    no-obfuscate-method-name          do not obfuscate method names
    obfuscate-method-name                    obfuscate method names
    
    no-obfuscate-namespace-name       do not obfuscate namespace names
    obfuscate-namespace-name                 obfuscate namespace names
    
    no-obfuscate-label-name           do not obfuscate label names
    obfuscate-label-name                     obfuscate label names
    
    scramble-mode     identifier|hexa|numeric         force scramble mode
    scramble-length   length ( min=2; max = 16 for scramble_mode=identifier,
                                        max = 32 for scramble_mode = hexa or numeric)


## Credits
- [pk-fr](https://github.com/pk-fr) Thanks for obfuscation core!
- [PHP-Parser 1.x](https://github.com/nikic/PHP-Parser/tree/1.x)
- [nikic](https://github.com/nikic)


## License
MIT license 
