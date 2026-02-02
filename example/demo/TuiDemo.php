<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Color\AnsiColor;
use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Extension\Core\Widget\BarChart\Bar;
use Crumbls\Tui\Extension\Core\Widget\BarChart\BarGroup;
use Crumbls\Tui\Extension\Core\Widget\BarChartWidget;
use Crumbls\Tui\Extension\Core\Widget\BlockWidget;
use Crumbls\Tui\Extension\Core\Widget\Chart\Axis;
use Crumbls\Tui\Extension\Core\Widget\Chart\AxisBounds;
use Crumbls\Tui\Extension\Core\Widget\Chart\DataSet;
use Crumbls\Tui\Extension\Core\Widget\Chart\GraphType;
use Crumbls\Tui\Extension\Core\Widget\ChartWidget;
use Crumbls\Tui\Extension\Core\Widget\CompositeWidget;
use Crumbls\Tui\Extension\Core\Widget\GaugeWidget;
use Crumbls\Tui\Extension\Core\Widget\GridWidget;
use Crumbls\Tui\Extension\Core\Widget\List\ListItem;
use Crumbls\Tui\Extension\Core\Widget\ListWidget;
use Crumbls\Tui\Extension\Core\Widget\ParagraphWidget;
use Crumbls\Tui\Extension\Core\Widget\Scrollbar\ScrollbarOrientation;
use Crumbls\Tui\Extension\Core\Widget\Scrollbar\ScrollbarState;
use Crumbls\Tui\Extension\Core\Widget\ScrollbarWidget;
use Crumbls\Tui\Extension\Core\Widget\Sparkline\RenderDirection;
use Crumbls\Tui\Extension\Core\Widget\SparklineWidget;
use Crumbls\Tui\Extension\Core\Widget\Table\TableRow;
use Crumbls\Tui\Extension\Core\Widget\TableWidget;
use Crumbls\Tui\Extension\Core\Widget\TabsWidget;
use Crumbls\Tui\Laravel\TuiCommand;
use Crumbls\Tui\Layout\Constraint;
use Crumbls\Tui\Style\Modifier;
use Crumbls\Tui\Style\Style;
use Crumbls\Tui\Text\Line;
use Crumbls\Tui\Text\Span;
use Crumbls\Tui\Text\Title;
use Crumbls\Tui\Widget\Borders;
use Crumbls\Tui\Widget\Direction;
use Crumbls\Tui\Widget\Widget;
use PhpTui\Term\Event\CharKeyEvent;
use PhpTui\Term\Event\CodedKeyEvent;
use PhpTui\Term\Event\MouseEvent;
use PhpTui\Term\KeyCode;

class TuiDemo extends TuiCommand
{
    protected $signature = 'tui:demo';

    protected $description = 'Visual demonstration of all TUI components';

    private int $currentScreen = 0;

    private int $tick = 0;

    private int $listSelection = 0;

    private int $tableSelection = 0;

    private float $gaugeProgress = 0.0;

    /** @var array<int, array{name: string, method: string}> */
    private array $screens = [
        ['name' => 'Overview', 'method' => 'renderOverview'],
        ['name' => 'Block', 'method' => 'renderBlock'],
        ['name' => 'Paragraph', 'method' => 'renderParagraph'],
        ['name' => 'List', 'method' => 'renderList'],
        ['name' => 'Table', 'method' => 'renderTable'],
        ['name' => 'Tabs', 'method' => 'renderTabs'],
        ['name' => 'Gauge', 'method' => 'renderGauge'],
        ['name' => 'Sparkline', 'method' => 'renderSparkline'],
        ['name' => 'BarChart', 'method' => 'renderBarChart'],
        ['name' => 'Chart', 'method' => 'renderChart'],
        ['name' => 'Scrollbar', 'method' => 'renderScrollbar'],
        ['name' => 'Layout', 'method' => 'renderLayout'],
        ['name' => 'Styles', 'method' => 'renderStyles'],
    ];

    protected function init(): void
    {
        $this->every(0.1, function (): void {
            $this->tick++;
            $this->gaugeProgress = ($this->tick % 100) / 100;
        });
    }

    protected function render(Area $area): ?Widget
    {
        $method = $this->screens[$this->currentScreen]['method'];
        $content = $this->$method();

        return GridWidget::default()
            ->direction(Direction::Vertical)
            ->constraints(
                Constraint::length(3),
                Constraint::min(0),
                Constraint::length(1),
            )
            ->widgets(
                $this->renderHeader(),
                $content,
                $this->renderFooter(),
            );
    }

    private function renderHeader(): Widget
    {
        $titles = array_map(
            fn(array $screen, int $i): Line => Line::fromString(
                $i === $this->currentScreen ? "[{$screen['name']}]" : " {$screen['name']} "
            ),
            $this->screens,
            array_keys($this->screens)
        );

        $tabs = TabsWidget::fromTitles(...$titles)
            ->select($this->currentScreen)
            ->highlightStyle(
                Style::default()
                    ->fg(AnsiColor::Yellow)
                    ->addModifier(Modifier::BOLD)
            );

        return BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('PHP TUI Components Demo'))
            ->widget($tabs);
    }

    private function renderFooter(): Widget
    {
        return ParagraphWidget::fromString(
            ' [<-/->] Navigate screens  [j/k] Select items  [q/ESC] Quit  Screen: ' .
            ($this->currentScreen + 1) . '/' . count($this->screens)
        )->style(Style::default()->fg(AnsiColor::DarkGray));
    }

    private function renderOverview(): Widget
    {
        $leftColumn = GridWidget::default()
            ->direction(Direction::Vertical)
            ->constraints(Constraint::percentage(50), Constraint::percentage(50))
            ->widgets($this->miniList(), $this->miniGauge());

        $rightColumn = GridWidget::default()
            ->direction(Direction::Vertical)
            ->constraints(Constraint::percentage(50), Constraint::percentage(50))
            ->widgets($this->miniBarChart(), $this->miniSparkline());

        return GridWidget::default()
            ->direction(Direction::Horizontal)
            ->constraints(Constraint::percentage(50), Constraint::percentage(50))
            ->widgets($leftColumn, $rightColumn);
    }

    private function miniList(): Widget
    {
        return BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('ListWidget'))
            ->widget(
                ListWidget::default()
                    ->items(
                        ListItem::fromString('Item One'),
                        ListItem::fromString('Item Two'),
                        ListItem::fromString('Item Three'),
                    )
                    ->select($this->tick % 3)
                    ->highlightStyle(Style::default()->fg(AnsiColor::Yellow))
                    ->highlightSymbol('> ')
            );
    }

    private function miniGauge(): Widget
    {
        return BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('GaugeWidget'))
            ->widget(
                GaugeWidget::default()
                    ->ratio($this->gaugeProgress)
                    ->style(Style::default()->fg(AnsiColor::Green)->bg(AnsiColor::DarkGray))
            );
    }

    private function miniBarChart(): Widget
    {
        $values = [
            'A' => ($this->tick + 10) % 20 + 5,
            'B' => ($this->tick + 5) % 20 + 5,
            'C' => $this->tick % 20 + 5,
            'D' => ($this->tick + 15) % 20 + 5,
        ];

        return BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('BarChartWidget'))
            ->widget(
                BarChartWidget::default()
                    ->barWidth(3)
                    ->barGap(1)
                    ->data(BarGroup::fromArray($values))
                    ->barStyle(Style::default()->fg(AnsiColor::Cyan))
            );
    }

    private function miniSparkline(): Widget
    {
        $data = [];
        for ($i = 0; $i < 30; $i++) {
            $data[] = (int) abs(sin(($this->tick + $i) * 0.2) * 10);
        }

        return BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('SparklineWidget'))
            ->widget(
                SparklineWidget::fromData(...$data)
                    ->style(Style::default()->fg(AnsiColor::Magenta))
            );
    }

    private function renderBlock(): Widget
    {
        $block1 = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('All Borders'))
            ->widget(ParagraphWidget::fromString('This block has all borders with a title.'));

        $block2 = BlockWidget::default()
            ->borders(Borders::LEFT | Borders::RIGHT)
            ->titles(Title::fromString('Left/Right'))
            ->widget(ParagraphWidget::fromString('Only left and right borders.'));

        $block3 = BlockWidget::default()
            ->borders(Borders::TOP | Borders::BOTTOM)
            ->titles(Title::fromString('Top/Bottom'))
            ->widget(ParagraphWidget::fromString('Only top and bottom borders.'));

        return GridWidget::default()
            ->direction(Direction::Horizontal)
            ->constraints(
                Constraint::percentage(33),
                Constraint::percentage(33),
                Constraint::percentage(34),
            )
            ->widgets($block1, $block2, $block3);
    }

    private function renderParagraph(): Widget
    {
        $para1 = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('Plain Text'))
            ->widget(
                ParagraphWidget::fromString(
                    "This is a simple paragraph widget.\n\n" .
                    "It supports multiple lines of text and automatic word wrapping " .
                    "when the content exceeds the available width of the container. " .
                    "The text will flow naturally to the next line."
                )
            );

        $para2 = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('Styled Text'))
            ->widget(
                ParagraphWidget::fromString(
                    "Paragraphs can display text with various styles.\n\n" .
                    "Current tick: " . $this->tick . "\n" .
                    "Progress: " . number_format($this->gaugeProgress * 100, 1) . "%"
                )->style(Style::default()->fg(AnsiColor::Cyan))
            );

        return GridWidget::default()
            ->direction(Direction::Vertical)
            ->constraints(Constraint::percentage(50), Constraint::percentage(50))
            ->widgets($para1, $para2);
    }

    private function renderList(): Widget
    {
        $items = [
            'First Item',
            'Second Item',
            'Third Item',
            'Fourth Item',
            'Fifth Item',
            'Sixth Item',
            'Seventh Item',
            'Eighth Item',
        ];

        $list1 = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('Basic List (j/k to navigate)'))
            ->widget(
                ListWidget::default()
                    ->items(...array_map(
                        fn(string $item): ListItem => ListItem::fromString($item),
                        $items
                    ))
                    ->select($this->listSelection)
                    ->highlightStyle(
                        Style::default()
                            ->fg(AnsiColor::Yellow)
                            ->addModifier(Modifier::BOLD)
                    )
                    ->highlightSymbol('>> ')
            );

        $styledItems = [
            ListItem::fromString('Normal item'),
            ListItem::fromString('Green item')->style(Style::default()->fg(AnsiColor::Green)),
            ListItem::fromString('Red item')->style(Style::default()->fg(AnsiColor::Red)),
            ListItem::fromString('Blue item')->style(Style::default()->fg(AnsiColor::Blue)),
            ListItem::fromString('Yellow item')->style(Style::default()->fg(AnsiColor::Yellow)),
            ListItem::fromString('Cyan item')->style(Style::default()->fg(AnsiColor::Cyan)),
        ];

        $list2 = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('Styled List Items'))
            ->widget(
                ListWidget::default()
                    ->items(...$styledItems)
                    ->highlightSymbol('* ')
            );

        return GridWidget::default()
            ->direction(Direction::Horizontal)
            ->constraints(Constraint::percentage(50), Constraint::percentage(50))
            ->widgets($list1, $list2);
    }

    private function renderTable(): Widget
    {
        $table = TableWidget::default()
            ->header(TableRow::fromStrings('ID', 'Name', 'Status', 'Progress'))
            ->rows(
                TableRow::fromStrings('1', 'Task Alpha', 'Running', '75%'),
                TableRow::fromStrings('2', 'Task Beta', 'Pending', '0%'),
                TableRow::fromStrings('3', 'Task Gamma', 'Complete', '100%'),
                TableRow::fromStrings('4', 'Task Delta', 'Running', '45%'),
                TableRow::fromStrings('5', 'Task Epsilon', 'Failed', '23%'),
                TableRow::fromStrings('6', 'Task Zeta', 'Running', '89%'),
            )
            ->widths(
                Constraint::length(5),
                Constraint::min(15),
                Constraint::length(10),
                Constraint::length(10),
            )
            ->select($this->tableSelection)
            ->highlightStyle(
                Style::default()
                    ->fg(AnsiColor::Black)
                    ->bg(AnsiColor::Yellow)
            )
            ->highlightSymbol('> ');

        return BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('TableWidget (j/k to navigate)'))
            ->widget($table);
    }

    private function renderTabs(): Widget
    {
        $tabs1 = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('Basic Tabs'))
            ->widget(
                TabsWidget::fromTitles(
                    Line::fromString('Tab 1'),
                    Line::fromString('Tab 2'),
                    Line::fromString('Tab 3'),
                )
                    ->select($this->tick % 3)
            );

        $tabs2 = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('Styled Tabs'))
            ->widget(
                TabsWidget::fromTitles(
                    Line::fromString('Home'),
                    Line::fromString('Settings'),
                    Line::fromString('Profile'),
                    Line::fromString('Help'),
                )
                    ->select((int) (($this->tick / 2) % 4))
                    ->highlightStyle(
                        Style::default()
                            ->fg(AnsiColor::Green)
                            ->addModifier(Modifier::BOLD)
                    )
                    ->divider(Span::fromString(' | '))
            );

        $tabs3 = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('Custom Divider'))
            ->widget(
                TabsWidget::fromTitles(
                    Line::fromString('Files'),
                    Line::fromString('Edit'),
                    Line::fromString('View'),
                )
                    ->select((int) (($this->tick / 3) % 3))
                    ->highlightStyle(Style::default()->fg(AnsiColor::Cyan))
                    ->divider(Span::fromString(' <> '))
            );

        $description = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('Description'))
            ->widget(
                ParagraphWidget::fromString(
                    "TabsWidget displays a horizontal set of tabs.\n\n" .
                    "Features:\n" .
                    "- Customizable highlight styles\n" .
                    "- Custom dividers between tabs\n" .
                    "- Selection state management\n" .
                    "- Automatic tab cycling animation shown above"
                )
            );

        return GridWidget::default()
            ->direction(Direction::Vertical)
            ->constraints(
                Constraint::length(3),
                Constraint::length(3),
                Constraint::length(3),
                Constraint::min(0),
            )
            ->widgets($tabs1, $tabs2, $tabs3, $description);
    }

    private function renderGauge(): Widget
    {
        $gauge1 = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('Default Gauge'))
            ->widget(
                GaugeWidget::default()
                    ->ratio($this->gaugeProgress)
            );

        $gauge2 = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('Green Gauge'))
            ->widget(
                GaugeWidget::default()
                    ->ratio($this->gaugeProgress)
                    ->style(Style::default()->fg(AnsiColor::Green)->bg(AnsiColor::DarkGray))
            );

        $gauge3 = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('Custom Label'))
            ->widget(
                GaugeWidget::default()
                    ->ratio($this->gaugeProgress)
                    ->label(Span::fromString('Loading... ' . number_format($this->gaugeProgress * 100, 0) . '%'))
                    ->style(Style::default()->fg(AnsiColor::Cyan)->bg(AnsiColor::Black))
            );

        $gauge4 = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('Red/Yellow Gauge'))
            ->widget(
                GaugeWidget::default()
                    ->ratio(1 - $this->gaugeProgress)
                    ->style(Style::default()->fg(AnsiColor::Red)->bg(AnsiColor::Yellow))
            );

        $description = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('About GaugeWidget'))
            ->widget(
                ParagraphWidget::fromString(
                    "GaugeWidget displays a progress bar.\n\n" .
                    "- Ratio from 0.0 to 1.0\n" .
                    "- Customizable foreground/background colors\n" .
                    "- Optional custom label\n" .
                    "- Auto-generated percentage label by default"
                )
            );

        return GridWidget::default()
            ->direction(Direction::Vertical)
            ->constraints(
                Constraint::length(3),
                Constraint::length(3),
                Constraint::length(3),
                Constraint::length(3),
                Constraint::min(0),
            )
            ->widgets($gauge1, $gauge2, $gauge3, $gauge4, $description);
    }

    private function renderSparkline(): Widget
    {
        $sineData = [];
        $cosineData = [];
        $randomData = [];

        for ($i = 0; $i < 50; $i++) {
            $sineData[] = (int) abs(sin(($this->tick + $i) * 0.15) * 20);
            $cosineData[] = (int) abs(cos(($this->tick + $i) * 0.1) * 15);
            $randomData[] = ($this->tick + $i * 7) % 25;
        }

        $spark1 = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('Sine Wave'))
            ->widget(
                SparklineWidget::fromData(...$sineData)
                    ->style(Style::default()->fg(AnsiColor::Green))
            );

        $spark2 = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('Cosine Wave'))
            ->widget(
                SparklineWidget::fromData(...$cosineData)
                    ->style(Style::default()->fg(AnsiColor::Cyan))
                    ->direction(RenderDirection::RightToLeft)
            );

        $spark3 = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('Pseudo-Random'))
            ->widget(
                SparklineWidget::fromData(...$randomData)
                    ->style(Style::default()->fg(AnsiColor::Magenta))
            );

        $description = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('SparklineWidget'))
            ->widget(
                ParagraphWidget::fromString(
                    "SparklineWidget renders compact inline charts.\n\n" .
                    "Features:\n" .
                    "- Array of integer data points\n" .
                    "- Direction: LeftToRight, RightToLeft\n" .
                    "- Automatic scaling to max value\n" .
                    "- Uses unicode block characters"
                )
            );

        return GridWidget::default()
            ->direction(Direction::Vertical)
            ->constraints(
                Constraint::length(5),
                Constraint::length(5),
                Constraint::length(5),
                Constraint::min(0),
            )
            ->widgets($spark1, $spark2, $spark3, $description);
    }

    private function renderBarChart(): Widget
    {
        $values1 = [
            'Mon' => ($this->tick + 5) % 30 + 10,
            'Tue' => ($this->tick + 10) % 30 + 10,
            'Wed' => ($this->tick + 15) % 30 + 10,
            'Thu' => ($this->tick + 20) % 30 + 10,
            'Fri' => ($this->tick + 25) % 30 + 10,
        ];

        $chart1 = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('Vertical Bar Chart'))
            ->widget(
                BarChartWidget::default()
                    ->barWidth(5)
                    ->barGap(2)
                    ->data(BarGroup::fromArray($values1))
                    ->barStyle(Style::default()->fg(AnsiColor::Green))
                    ->labelStyle(Style::default()->fg(AnsiColor::Yellow))
                    ->valueStyle(Style::default()->fg(AnsiColor::White))
            );

        $chart2 = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('Horizontal Bar Chart'))
            ->widget(
                BarChartWidget::default()
                    ->barWidth(1)
                    ->barGap(1)
                    ->direction(Direction::Horizontal)
                    ->data(
                        BarGroup::fromBars(
                            Bar::fromValue(($this->tick + 5) % 50 + 10)->label(Line::fromString('Alpha')),
                            Bar::fromValue(($this->tick + 10) % 50 + 10)->label(Line::fromString('Beta')),
                            Bar::fromValue(($this->tick + 15) % 50 + 10)->label(Line::fromString('Gamma')),
                            Bar::fromValue(($this->tick + 20) % 50 + 10)->label(Line::fromString('Delta')),
                        )
                    )
                    ->barStyle(Style::default()->fg(AnsiColor::Cyan))
            );

        return GridWidget::default()
            ->direction(Direction::Horizontal)
            ->constraints(Constraint::percentage(50), Constraint::percentage(50))
            ->widgets($chart1, $chart2);
    }

    private function renderChart(): Widget
    {
        $sineData = [];
        $cosineData = [];

        for ($i = 0; $i <= 100; $i += 2) {
            $x = $i / 10.0;
            $sineData[] = [$x, sin($x + $this->tick * 0.1) * 2 + 3];
            $cosineData[] = [$x, cos($x + $this->tick * 0.1) * 2 + 3];
        }

        $chart = ChartWidget::new()
            ->xAxis(
                Axis::default()
                    ->bounds(AxisBounds::new(0.0, 10.0))
                    ->labels(
                        Span::fromString('0'),
                        Span::fromString('5'),
                        Span::fromString('10'),
                    )
            )
            ->yAxis(
                Axis::default()
                    ->bounds(AxisBounds::new(0.0, 6.0))
                    ->labels(
                        Span::fromString('0'),
                        Span::fromString('3'),
                        Span::fromString('6'),
                    )
            )
            ->datasets(
                DataSet::new('sin(x)')
                    ->data($sineData)
                    ->marker(Marker::Braille)
                    ->graphType(GraphType::Line)
                    ->style(Style::default()->fg(AnsiColor::Green)),
                DataSet::new('cos(x)')
                    ->data($cosineData)
                    ->marker(Marker::Braille)
                    ->graphType(GraphType::Line)
                    ->style(Style::default()->fg(AnsiColor::Cyan)),
            );

        $chartBlock = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('ChartWidget - Line Graphs'))
            ->widget($chart);

        $description = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('About ChartWidget'))
            ->widget(
                ParagraphWidget::fromString(
                    "ChartWidget renders scatter and line graphs.\n\n" .
                    "Features:\n" .
                    "- Multiple datasets with different styles\n" .
                    "- X and Y axis with labels and bounds\n" .
                    "- Graph types: Scatter, Line\n" .
                    "- Markers: Dot, Block, Bar, Braille, HalfBlock"
                )
            );

        return GridWidget::default()
            ->direction(Direction::Vertical)
            ->constraints(Constraint::percentage(60), Constraint::percentage(40))
            ->widgets($chartBlock, $description);
    }

    private function renderScrollbar(): Widget
    {
        $content1 = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('Vertical Scrollbar'))
            ->widget(
                ParagraphWidget::fromString(
                    str_repeat("Line of content for scrolling demonstration.\n", 20)
                )
            );

        $vScrollbar = ScrollbarWidget::default()
            ->orientation(ScrollbarOrientation::VerticalRight)
            ->state(new ScrollbarState(
                contentLength: 20,
                position: $this->tick % 20,
            ));

        $leftPane = CompositeWidget::fromWidgets($content1, $vScrollbar);

        $content2 = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('Horizontal Scrollbar'))
            ->widget(
                ParagraphWidget::fromString(
                    "This content area demonstrates horizontal scrollbar.\n" .
                    "The scrollbar position animates automatically."
                )
            );

        $hScrollbar = ScrollbarWidget::default()
            ->orientation(ScrollbarOrientation::HorizontalBottom)
            ->state(new ScrollbarState(
                contentLength: 50,
                position: $this->tick % 50,
            ));

        $rightPane = CompositeWidget::fromWidgets($content2, $hScrollbar);

        return GridWidget::default()
            ->direction(Direction::Horizontal)
            ->constraints(Constraint::percentage(50), Constraint::percentage(50))
            ->widgets($leftPane, $rightPane);
    }

    private function renderLayout(): Widget
    {
        $description = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('Layout Constraints'))
            ->widget(
                ParagraphWidget::fromString(
                    'Constraints: length(fixed), percentage(%), min(minimum), max(maximum)'
                )
            );

        $box1 = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('length(20)'))
            ->widget(ParagraphWidget::fromString('Fixed 20 cols'));

        $box2 = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('percentage(50)'))
            ->widget(ParagraphWidget::fromString('50% of remaining'));

        $box3a = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('min(10) top'))
            ->widget(ParagraphWidget::fromString('Nested'));

        $box3b = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('min(10) bot'))
            ->widget(ParagraphWidget::fromString('Layout'));

        $nestedColumn = GridWidget::default()
            ->direction(Direction::Vertical)
            ->constraints(Constraint::percentage(50), Constraint::percentage(50))
            ->widgets($box3a, $box3b);

        $mainRow = GridWidget::default()
            ->direction(Direction::Horizontal)
            ->constraints(
                Constraint::length(20),
                Constraint::percentage(50),
                Constraint::min(10),
            )
            ->widgets($box1, $box2, $nestedColumn);

        return GridWidget::default()
            ->direction(Direction::Vertical)
            ->constraints(Constraint::length(3), Constraint::min(0))
            ->widgets($description, $mainRow);
    }

    private function renderStyles(): Widget
    {
        $colors = [
            'Black' => AnsiColor::Black,
            'Red' => AnsiColor::Red,
            'Green' => AnsiColor::Green,
            'Yellow' => AnsiColor::Yellow,
            'Blue' => AnsiColor::Blue,
            'Magenta' => AnsiColor::Magenta,
            'Cyan' => AnsiColor::Cyan,
            'White' => AnsiColor::White,
        ];

        $colorItems = array_map(
            fn(string $name, AnsiColor $color): ListItem => ListItem::fromString($name)
                ->style(Style::default()->fg($color)),
            array_keys($colors),
            array_values($colors)
        );

        $colorList = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('ANSI Colors'))
            ->widget(
                ListWidget::default()
                    ->items(...$colorItems)
            );

        $modifierItems = [
            ListItem::fromString('Bold')->style(Style::default()->addModifier(Modifier::BOLD)),
            ListItem::fromString('Dim')->style(Style::default()->addModifier(Modifier::DIM)),
            ListItem::fromString('Italic')->style(Style::default()->addModifier(Modifier::ITALIC)),
            ListItem::fromString('Underlined')->style(Style::default()->addModifier(Modifier::UNDERLINED)),
            ListItem::fromString('Reversed')->style(Style::default()->addModifier(Modifier::REVERSED)),
        ];

        $modifierList = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('Style Modifiers'))
            ->widget(
                ListWidget::default()
                    ->items(...$modifierItems)
            );

        $description = BlockWidget::default()
            ->borders(Borders::ALL)
            ->titles(Title::fromString('Styling System'))
            ->widget(
                ParagraphWidget::fromString(
                    "The styling system supports:\n\n" .
                    "- 16 ANSI colors (8 normal + 8 bright)\n" .
                    "- RGB colors (24-bit true color)\n" .
                    "- Linear gradients\n" .
                    "- Modifiers: BOLD, DIM, ITALIC, UNDERLINED, REVERSED, etc.\n" .
                    "- Foreground, background, and underline colors"
                )
            );

        return GridWidget::default()
            ->direction(Direction::Vertical)
            ->constraints(
                Constraint::length(5),
                Constraint::length(5),
                Constraint::min(0),
            )
            ->widgets($colorList, $modifierList, $description);
    }

    protected function handleEvent(CharKeyEvent|CodedKeyEvent|MouseEvent $event): void
    {
        if ($event instanceof CodedKeyEvent) {
            match ($event->code) {
                KeyCode::Left => $this->previousScreen(),
                KeyCode::Right => $this->nextScreen(),
                KeyCode::Up, KeyCode::Down => $this->handleVerticalNavigation($event->code),
                KeyCode::Esc => $this->quit(),
                default => null,
            };
        }

        if ($event instanceof CharKeyEvent) {
            match ($event->char) {
                'q' => $this->quit(),
                'h' => $this->previousScreen(),
                'l' => $this->nextScreen(),
                'j' => $this->selectNext(),
                'k' => $this->selectPrevious(),
                default => null,
            };
        }
    }

    private function previousScreen(): void
    {
        $this->currentScreen = ($this->currentScreen - 1 + count($this->screens)) % count($this->screens);
        $this->listSelection = 0;
        $this->tableSelection = 0;
    }

    private function nextScreen(): void
    {
        $this->currentScreen = ($this->currentScreen + 1) % count($this->screens);
        $this->listSelection = 0;
        $this->tableSelection = 0;
    }

    private function handleVerticalNavigation(KeyCode $code): void
    {
        if ($code === KeyCode::Down) {
            $this->selectNext();
        } else {
            $this->selectPrevious();
        }
    }

    private function selectNext(): void
    {
        $this->listSelection = min($this->listSelection + 1, 7);
        $this->tableSelection = min($this->tableSelection + 1, 5);
    }

    private function selectPrevious(): void
    {
        $this->listSelection = max($this->listSelection - 1, 0);
        $this->tableSelection = max($this->tableSelection - 1, 0);
    }
}
