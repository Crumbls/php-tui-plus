<?php

declare(strict_types=1);

use Crumbls\Tui\Canvas\CanvasContext;
use Crumbls\Tui\Canvas\Marker;
use Crumbls\Tui\Display\Area;
use Crumbls\Tui\Display\Buffer;
use Crumbls\Tui\Extension\Core\Shape\MapResolution;
use Crumbls\Tui\Extension\Core\Shape\MapShape;
use Crumbls\Tui\Extension\Core\Widget\CanvasWidget;
use Crumbls\Tui\Extension\Core\Widget\Chart\AxisBounds;

test('map low resolution', function (): void {
    $expected = [
        '                                                                                ',
        '                   ••••••• •• •• •• •                                           ',
        '            ••••••••••••••       •••      ••••  •••  ••    ••••                 ',
        '            ••••••••••••••••     ••                ••• ••••••• •• •• •••        ',
        '• • •• •••••• •••••••••••• ••   •••  •    •••••  •••••••••          ••  • • • • ',
        '•••••       ••••  •••••••• •• ••  •••    •••• ••••    •• •                    • ',
        '   ••••••••  ••••••• •••••  •••       ••••••••                        • •••••   ',
        '  •• ••   ••    •••••••  ••          ••• ••••                        ••    •    ',
        '•••       •••    •••••• ••••         ••••                             •• •   •• ',
        '            •      •••••••••          ••  •   ••• • •• ••            ••         ',
        '            • •     ••••             •• ••••••••• •••   •         • • ••        ',
        '            •         •               ••••• ••••  ••             ••••••         ',
        '             •      ••               •   • •• •                  •••••          ',
        '              ••  •• •              •         ••  ••              •             ',
        '    ••        •••   •••            •           •  •••••    •   •••              ',
        '     •           •••• •••                       •   •  •    •  • ••             ',
        '                  •••• •           •            •• •     •  ••   ••             ',
        '                     ••• ••         •           • •     ••   ••• •••            ',
        '                      •    •        • •• •              •   •   •  •            ',
        '                   •  •     •            •    • •            ••• •  •           ',
        '                     •        •           •   •              •• •   • •         ',
        '                               •                •              ••   ••• •       ',
        ' •                    •       •           •     • •                • •          ',
        '                        •                 •    • ••               •  • •   •  • ',
        '                              •                •                •       •       ',
        '                       •    •                 •  •              •        •      ',
        '                       •   ••              • •                  • • ••       •  ',
        '                       •  •                •                         ••••    •• ',
        '                       • •                                             ••   ••• ',
        '                       ••                                                   •   ',
        '                       •• •                                                     ',
        '                       ••                                                       ',
        '                                                                                ',
        '                        •••                        •      •••• • • •• •         ',
        '                       ••••           •••••• •••••• ••••••             • •••    ',
        '         •• •••••• ••••• ••      • ••• •                                   ••   ',
        '•  •••••             ••  •• ••••••                                         • •• ',
        '•    •                 •   •  •                                             • • ',
        '       •                                                                        ',
        '                                                                                ',
    ];

    $canvas = CanvasWidget::default()
        ->marker(Marker::Dot)
        ->xBounds(AxisBounds::new(-180, 180))
        ->yBounds(AxisBounds::new(-90, 90))
        ->paint(static function (CanvasContext $context): void {
            $context->draw(MapShape::default()->resolution(MapResolution::Low));
        });
    $area = Area::fromDimensions(80, 40);
    $buffer = Buffer::empty($area);
    render($buffer, $canvas);
    expect($buffer->toLines())->toBe($expected);
});

test('map high resolution', function (): void {
    $expected = [
        '                                                                                ',
        '                  ⢀⣠⠤⠤⠤⠔⢤⣤⡄⠤⡠⣄⠢⠂⢢⠰⣠⡄⣀⡀                      ⣀                   ',
        '            ⢀⣀⡤⣦⠲⢶⣿⣮⣿⡉⣰⢶⢏⡂        ⢀⣟⠁     ⢺⣻⢿⠏   ⠈⠉⠁ ⢀⣀    ⠈⠓⢳⣢⣂⡀               ',
        '            ⡞⣳⣿⣻⡧⣷⣿⣿⢿⢿⣧⡀⠉⠉⠙⢆      ⣰⠇               ⣠⠞⠃⢉⣄⣀⣠⠴⠊⠉⠁ ⠐⠾⠤⢤⠤⡄⠐⣻⠜⢓⠂      ',
        '⢍ ⢀⡴⠊⠙⠓⠒⠒⠤⠖⠺⠿⠽⣷⣬⢬⣾⣷⢻⣷⢲⢲⣍⠱⡀ ⠹⡗   ⢀⢐⠟        ⡔⠒⠉⠲⠤⢀⢄⡀⢩⣣⠦⢷⢼⡏⠈          ⠉⠉⠉ ⠈⠈⠉⠖⠤⠆⠒⠭',
        '⠶⢽⡲⣽⡆             ⠈⣠⣽⣯⡼⢯⣘⡯⠃⠘⡆ ⢰⠒⠁ ⢾⣚⠟    ⢀⠆ ⣔⠆ ⢷⠾⠋⠁    ⠙⠁                     ⠠⡤',
        '  ⠠⢧⣄⣀⡶⠦⠤⡀        ⢰⡁ ⠉⡻⠙⣎⡥  ⠘⠲⠇       ⢀⡀⠨⣁⡄⣸⢫⡤⠄                        ⣀⢠⣤⠊⣼⠅⠖⠋⠁',
        '   ⣠⠾⠛⠁  ⠈⣱        ⠋⠦⢤⡼ ⠈⠈⠦⡀         ⢀⣿⣇ ⢹⣷⣂⡞⠃                       ⢀⣂⡀  ⠏⣜    ',
        '          ⠙⣷⡄        ⠘⠆ ⢀⣀⡠⣗         ⠘⣻⣽⡟⠉⠈                           ⢹⡇  ⠟⠁    ',
        '           ⠈⡟           ⢎⣻⡿⠾⠇         ⠘⠇  ⣀⡀  ⣤⣤⡆ ⡠⡦                 ⢀⠎⡏        ',
        '            ⡇          ⣀⠏⠋           ⢸⠒⢃⡖⢻⢟⣷⣄⣰⣡⠥⣱ ⢏⣧              ⣀ ⡴⠚⢰⠟        ',
        '            ⢳         ⢸⠃             ⠸⣄⣼⣠⢼⡴⡟⢿⢿⣀⣄  ⠸⡹             ⠘⡯⢿⡇⡠⢼⠁        ',
        '             ⢳⣀      ⢀⠞⠁             ⢠⠋⠁ ⠐⠧⡄⣬⣉⣈⡽                  ⢧⠘⢽⠟⠉         ',
        '              ⣿⣄  ⡴⠚⠛⣿⣀             ⢠⠖     ⠈⠁ ⠹⣧  ⢾⣄⡀             ⡼ ⠈           ',
        '    ⣀         ⠘⣿⡄ ⡇  ⣘⣻             ⡏          ⢻⡄ ⠘⠿⢿⠒⠲⡀   ⢀⡀   ⢀⡰⣗             ',
        '    ⠉⠷          ⢫⡀⢧⡼⡟⠉⣛⣳⣦⡀         ⠈⡇          ⠸⣱  ⢀⡼  ⢺  ⡸⠉⢇  ⣾⡏ ⣁             ',
        '                 ⠉⠒⢆⡓⡆             ⠠⡃           ⢳⣇⡠⠏   ⠐⡄⡞  ⠘⣇⡀⢱  ⣾⡀            ',
        '                    ⢹⣇⣀⣾⡷⠤⡆         ⢣            ⠯⢺⠇    ⢣⣅   ⣽⢱⡔ ⢠⢿⣗            ',
        '                     ⠙⢱   ⠘⠦⡄       ⠈⢦⡠⣠⢶⣀        ⡜     ⠈⠿  ⢠⣽⢆ ⢀⣼⡜⠿            ',
        '                     ⢀⡞     ⢱⡀           ⢸       ⡔⠁          ⢻⢿⢰⠏⢸⣤⣴⣆           ',
        '                     ⢘⠆      ⠙⠢⢄         ⠸⡀     ⡸⠁           ⠈⣞⡎⠥⡟⣿⠠⠿⣷⠒⢤⢀⣆      ',
        '                     ⠘⠆        ⢈⠂         ⢳     ⡇             ⠈⠳⠶⣤⣭⣠ ⠋⢧⡬⣟⠉⠷⡄    ',
        '                      ⢨        ⡜          ⢸     ⠸ ⣠               ⠁⢁⣰⢶ ⡇⠉⠁ ⠛    ',
        '⠆                     ⠈⢱⡀      ⡆          ⡇    ⢀⡜⡴⢹               ⢰⠏⠁⠘⢶⠹⡀   ⠸ ⢠⡶',
        '                        ⠅     ⣸           ⢸    ⢫ ⡞⡊             ⢠⠔⠋     ⢳⡀ ⠐⣦   ',
        '                        ⡅    ⡏            ⠈⡆  ⢠⠎ ⠳⠃             ⢸        ⢳      ',
        '                       ⠨    ⡸⠁             ⢱  ⡸                 ⠈⡇ ⢀⣀⡀   ⢸      ',
        '                       ⠸  ⠐⡶⠁              ⠘⠖⠚                   ⠣⠒⠋ ⠱⣇ ⢀⠇   ⠰⡄ ',
        '                       ⠽ ⣰⡖⠁                                          ⠘⢚⡊    ⢀⣿⠇',
        '                       ⡯⢀⡟                                             ⠘⠏   ⢠⢾⠃ ',
        '                       ⠇⢨⠆                            ⢠⡄                    ⠈⠁  ',
        '                       ⢧⣷⡀⠚                                                     ',
        '                        ⠉⠁                                                      ',
        '                          ⢀⡀                                                    ',
        '                        ⢠⡾⠋                      ⣀⡠⠖⢦⣀⣀  ⣀⠤⠦⢤⠤⠶⠤⠖⠦⠤⠤⠤⠴⠤⢤⣄       ',
        '                ⢀⣤⣀ ⡀  ⣼⣻⠙⡆         ⢀⡤⠤⠤⠴⠒⠖⠒⠒⠒⠚⠉⠋⠁    ⢰⡳⠊⠁              ⠈⠉⠉⠒⠤⣤  ',
        '    ⢀⣀⣀⡴⠖⠒⠒⠚⠛⠛⠛⠒⠚⠳⠉⠉⠉⠉⢉⣉⡥⠔⠃     ⢀⣠⠤⠴⠃                                      ⢠⠞⠁  ',
        '   ⠘⠛⣓⣒⠆              ⠸⠥⣀⣤⡦⠠⣞⣭⣇⣘⠿⠆                                         ⣖⠛   ',
        '⠶⠔⠲⠤⠠⠜⢗⠤⠄                 ⠘⠉  ⠁                                            ⠈⠉⠒⠔⠤',
        '                                                                                ',
    ];

    $canvas = CanvasWidget::default()
        ->marker(Marker::Braille)
        ->xBounds(AxisBounds::new(-180, 180))
        ->yBounds(AxisBounds::new(-90, 90))
        ->paint(static function (CanvasContext $context): void {
            $context->draw(MapShape::default()->resolution(MapResolution::High));
        });
    $area = Area::fromDimensions(80, 40);
    $buffer = Buffer::empty($area);
    render($buffer, $canvas);
    expect($buffer->toLines())->toBe($expected);
});
