# GradientImageToolEx

Mit dem Tool lassen sich einfach Bilder erstellen, in denen multiple Farbverläufe zu sehen sind. Bei Bedarf kann auch ein bestimmter Punkt mit einem schwarzen Strich markiert werden.

## Beispiel 1:
<img src="http://storage6.static.itmages.com/i/16/0523/h_1464022041_4878901_58c77ac2e4.jpeg">

Code:
<pre>
    $labtool = new DamianSchwyrz\GradientImageTool\GradientImageTool(1000, 70);

    $labtool->add_position(0)->add_color('f80a19');

    $labtool->add_position(32)->add_color('f8e800');
    $labtool->add_position(35)->add_color('00862d');
    $labtool->add_position(38)->add_color('f8e800');

    $labtool->add_position(50)->add_color('f80a19');

    $labtool->add_position(82)->add_color('f8e800');
    $labtool->add_position(85)->add_color('00862d');
    $labtool->add_position(88)->add_color('f8e800');

    $labtool->add_position(100)->add_color('f80a19');

    $labtool->calculate_rel_to_abs();

    $labtool->set_min_value(200);
    $labtool->set_max_value(500);
    $labtool->set_value(305);

    $labtool->create_image();

    $labtool->fill_with_gradient();
    $labtool->draw_value();

    $labtool->draw();
</pre>

## Beispiel 2:
<img src="http://storage2.static.itmages.com/i/16/0523/h_1464022382_6354313_e4cb12d084.jpeg">

Code:
<pre>
    $labtool = new DamianSchwyrz\GradientImageTool\GradientImageTool(500, 500);

    $labtool->add_position(0)->add_color('000');


    $labtool->add_position(50)->add_color('fff');


    $labtool->add_position(100)->add_color('000');

    $labtool->calculate_rel_to_abs();


    $labtool->create_image();

    $labtool->fill_with_gradient();


    $labtool->draw();
</pre>


## Tests:

<p>Für alle Methoden, die der Nutzer verwenden kann, um Optionen oder Daten festzulegen, existieren Tests. 100% ist der Code allerdings nicht abgedeckt.</p>

<pre>
    phpunit tests/
</pre>

<pre>
PHPUnit 5.3.2 by Sebastian Bergmann and contributors.

................                                                  16 / 16 (100%)

Time: 115 ms, Memory: 8.00Mb

OK (16 tests, 37 assertions)

</pre>
