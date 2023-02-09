![Alt text](docs/logo.png?raw=true "logo")


# Mehr Übersicht bei vielen Global Operations im Contao Backend
Falls ein Contao Backend Modul über viele globale Operationen verfügt, kann die Navigation schnell unübersichtlich werden.

Hier hilft diese Erweiterung für Contao. Die verschiedenen Menupunkte lassen sich thematisch grupieren und geordnet ausgeben.

![Alt text](docs/images/img.png?raw=true "logo")

## Konfiguration

Die Konfiguration erfolgt Contao-Typisch über das DCA. Beachten Sie dabei folgende zwei keys:

`custom_glob_op`:
Mit `custom_glob_op => true` aktivieren Sie customisierte Ausgabe des Menupunktes.
Mit dem Array `custom_glob_op_options => []` kann der Menupunnkt noch zusätzlich konfiguriert werden oder auch weggelassen werden.

```
	'global_operations' => [
        'all'                          => [
            'href'       => 'act=select',
            'class'      => 'header_edit_all',
            'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
        ],
        'glob_operation_one' => [
            'href'                   => 'action=glob_operation_one',
            'class'                  => 'glob_operation_one',
            'icon'                   => 'bundles/myawesomecontaobundle/icons/file-word-regular.svg',
            'attributes'             => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            'custom_glob_op'         => true,
            'custom_glob_op_options' => ['add_to_menu_group' => 'my_menu_one', 'sorting' => 10],
        ],
        'glob_operation_two' => [
            'href'                   => 'action=glob_operation_two',
            'class'                  => 'glob_operation_one',
            'icon'                   => 'bundles/myawesomecontaobundle/icons/file-excel-regular.svg',
            'attributes'             => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            'custom_glob_op'         => true,
            'custom_glob_op_options' => ['add_to_menu_group' => 'my_menu_one', 'sorting' => 20],
        ],
        'glob_operation_three' => [
            'href'                   => 'action=glob_operation_three',
            'class'                  => 'glob_operation_one',
            'icon'                   => 'bundles/myawesomecontaobundle/icons/file-pdf-regular.svg',
            'attributes'             => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            'custom_glob_op'         => true,
            'custom_glob_op_options' => ['add_to_menu_group' => 'my_menu_two', 'sorting' => 30],
        ],
	],
```
