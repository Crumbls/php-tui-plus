PHP TUI Plus
============

A comprehensive PHP TUI (Terminal User Interface) library, heavily inspired by Rust's [Ratatui](https://github.com/ratatui-org/ratatui). This is an enhanced fork with additional interactive widgets and Laravel integration.

Features
--------

- All core widgets and shapes from Ratatui
- Advanced terminal control inspired by Rust's [crossterm](https://github.com/crossterm-rs/crossterm)
- Font and image rendering
- Layout control using the Cassowary algorithm via [php-tui/cassowary](https://github.com/php-tui/cassowary)
- Laravel integration with Artisan command support
- Interactive form widgets (text input, select, modals)
- Data display widgets (tables, trees, charts)

Installation
------------

```bash
composer require crumbls/php-tui-plus
```

Widgets
-------

### Core Widgets (from Ratatui)

| Widget | Description |
|--------|-------------|
| `BlockWidget` | Container with borders, titles, and padding |
| `ParagraphWidget` | Text display with wrapping and alignment |
| `TableWidget` | Static data tables with headers |
| `ListWidget` | Scrollable list of items |
| `TabsWidget` | Tab navigation |
| `BarChartWidget` | Horizontal/vertical bar charts |
| `ChartWidget` | Line and scatter charts |
| `GaugeWidget` | Progress indicators |
| `SparklineWidget` | Compact inline charts |
| `CanvasWidget` | Drawing primitives and shapes |
| `GridWidget` | Layout grid system |
| `ScrollbarWidget` | Scrollbar indicators |

### Interactive Widgets (php-tui-plus additions)

| Widget | Description |
|--------|-------------|
| `TextInputWidget` | Single-line text input with cursor |
| `SelectWidget` | Dropdown/picker selection |
| `SelectableTableWidget` | Table with row selection and keyboard navigation |
| `DataTableWidget` | Sortable, filterable data tables |
| `TreeWidget` | Hierarchical tree view with expand/collapse |
| `ModalWidget` | Dialog boxes, confirmations, alerts |
| `StatusBarWidget` | Application status bar |
| `WizardWidget` | Multi-step form flows |

Laravel Usage
-------------

The package auto-registers its service provider. Create TUI commands by extending the base command:

```php
use Crumbls\Tui\Laravel\TuiCommand;

class MyTuiCommand extends TuiCommand
{
    protected $signature = 'my:tui';

    public function handle()
    {
        // Your TUI application logic
    }
}
```

View the demo code in `example/demo` for more examples.

Quick Example
-------------

```php
use Crumbls\Tui\DisplayBuilder;
use Crumbls\Tui\Extension\Core\Widget\BlockWidget;
use Crumbls\Tui\Extension\Core\Widget\ParagraphWidget;
use Crumbls\Tui\Text\Text;

$display = DisplayBuilder::default()->build();
$display->draw(
    BlockWidget::default()
        ->titles(Title::fromString('Hello World'))
        ->widget(
            ParagraphWidget::fromText(
                Text::parse('<fg=green>Welcome to PHP TUI Plus!</>')
            )
        )
);
```

Testing
-------

```bash
composer test
```

Documentation
-------------

- [Original PHP TUI Documentation](https://php-tui.github.io/php-tui)
- Component specs in `docs/components/`

Requirements
------------

- PHP 8.1+
- Terminal with ANSI support

Contributions
-------------

Contributions are welcome! Please submit pull requests or open issues on GitHub.

Credits
-------

- **Chase Miller** <chase@crumbls.com> - php-tui-plus enhancements and Laravel integration
- **Daniel Leech** - Original php-tui author

License
-------

MIT License