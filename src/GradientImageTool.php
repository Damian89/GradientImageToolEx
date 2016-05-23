<?php

namespace DamianSchwyrz\GradientImageTool;

/**
 * GradientImageTool erlaubt es verschiedene Farbverläufe mittels PHP zu generieren.
 *
 *
 * @package  DamianSchwyrz\GradientImageTool
 * @author   Damian Schwyrz <mail@damianschwyrz.de>
 * @version  1.0
 * @access   public
 */
class GradientImageTool
{

    /**
     * Breite des zu generierenden Bildes in Pixeln
     * 
     * @access private
     * @var int
     */
    private $width;

    /**
     * Höhe des zu generierenden Bildes in Pixeln
     * 
     * @access private
     * @var int
     */
    private $height;

    /**
     * Array, in dem sich die Position samt Farbe (Hex) zur Darstellung von 
     * Verläufen befinden
     * 
     * @access private
     * @var array
     */
    private $color_points = [];

    /**
     * Zu Beginn der Darstellung eines Bildes (0% Breite) kann man in $min den 
     * dazugehörigen absoluten Wert definieren. 
     * 
     * @access private
     * @var int|float
     */
    private $min = '';

    /**
     * Zum Ende der Darstellung eines Bildes (100% Breite) kann man in $max den 
     * dazugehörigen absoluten Wert definieren. 
     * 
     * @access private
     * @var int|float
     */
    private $max = '';

    /**
     * Zum Eintragen eines bestimmten Punktes (z.B. Messpunkt), der zwischen
     * $min und $max liegt, kann $value verwendet werden. 
     * 
     * @access private
     * @var int|float
     */
    private $value = '';

    /**
     * Hier wird das Bild temporär gespeichert 
     * 
     * @access private
     * @var resource
     */
    private $image;

    /**
     * Konstruktor der Klasse
     *
     * @param int $width Breite des Bildes
     * @param int $height Höhe des Bildes
     * @return void
     */
    public function __construct(int $width = 250, int $height = 25)
    {
        if ($width == 0 || $height == 0)
        {
            throw new \Exception('Fehler: Das Bild kann nicht in irgendeiner Dimension 0 Pixel groß sein!');
        }

        $this->width  = $width;
        $this->height = $height;
    }

    /**
     * Fügt in $color_points eine Position ein, bei der eine Farbe zu sehen
     * sein soll
     *
     * @param int $position Position relativ bezogen auf Breite des Bildes [%]
     * @return self
     */
    public function add_position(int $position)
    {
        if ($position < 0 || $position > 100)
        {
            throw new \Exception('Fehler: Position ist außerhalb des gültigen Bereichs (0 bis 100 Prozent)');
        }

        $this->color_points[] = [ 'position' => $position];

        return $this;
    }

    /**
     * Fügt der zuletzt hinzugefügten Position in $color_points eine Farbe
     * hinzu 
     *
     * @param string $hex
     * @return void
     */
    public function add_color(string $hex)
    {
        if (strpos($hex, '#') !== FALSE)
        {
            throw new \Exception('Fehler: Bitte HEX-Wert ohne Raute angeben!');
        }

        if (strlen($hex) != 3 && strlen($hex) != 6)
        {
            throw new \Exception('Fehler: Länge des Hex-Strings stimmt nicht!');
        }

        $last_key = key(array_slice($this->color_points, -1, 1, TRUE));

        if (!isset($this->color_points[$last_key]))
        {
            throw new \Exception('Fehler: Es fehlen Positionen für diese Farbe');
        }

        $this->color_points[$last_key] = array_merge($this->color_points[$last_key], $this->hex_to_rgb($hex));
    }

    /**
     * Rechnet relative Angaben in absolute um.
     *
     * @return void
     */
    public function calculate_rel_to_abs()
    {


        foreach ($this->color_points as $key => $values)
        {
            if ($values['position'] == 0)
            {
                $this->color_points[$key]['absolute'] = 0;
            }
            else
            {
                $this->color_points[$key]['absolute'] = round($this->width / 100 * $values['position']);
            }
        }
    }

    /**
     * Absoluter Minimalwert wird hiermit gesetzt 
     *
     * @param int|float $min
     * @return void
     */
    public function set_min_value($min = '')
    {
        if ($min != '')
        {
            $this->min = $min;

            $this->color_points[0]['value'] = $min;
        }
    }

    /**
     * Absoluter Maximalwert wird hiermit gesetzt 
     *
     * @param int|float $max
     * @return void
     */
    public function set_max_value($max = '')
    {
        if ($max != '')
        {
            $this->max = $max;
            $last_key  = key(array_slice($this->color_points, -1, 1, TRUE));

            $this->color_points[$last_key]['value'] = $max;
        }
    }

    /**
     * Hiermit lässt sich ein bestimmter Punkt auf der Grafik markieren, der
     * zwischen $min und $max liegt 
     *
     * @param int|float $eval
     * @return void
     */
    public function set_value($eval = '')
    {
        if ($eval != '')
        {
            $this->value = $eval;
        }
    }

    /**
     * Bild wird in genannter Größe erstellt
     *
     * @return void
     */
    public function create_image()
    {
        $this->image = imagecreatetruecolor($this->width, $this->height);
    }

    /**
     * Über die gesamte Breite, angefangen bei 0% bis 100% wird je eine 
     * vertikale Linie entlang der umgerechneten Pixel-Angabe gezogen. Die
     * Linie hat dabei eine bestimmte berechnete Farbe. In der Summe entsteht
     * so ein Farbverlauf.
     *
     * @return void
     */
    public function fill_with_gradient()
    {
        $steps         = count($this->color_points);
        $current_index = 0;

        if ($steps > 1)
        {

            while ($current_index < $steps)
            {
                if (isset($this->color_points[$current_index]) && isset($this->color_points[$current_index + 1]))
                {

                    $current_absolute_position = $this->color_points[$current_index]['absolute'];
                    $current_color_r           = $this->color_points[$current_index]['r'];
                    $current_color_g           = $this->color_points[$current_index]['g'];
                    $current_color_b           = $this->color_points[$current_index]['b'];


                    $last_absolute_position = $this->color_points[$current_index + 1]['absolute'];
                    $last_color_r           = $this->color_points[$current_index + 1]['r'];
                    $last_color_g           = $this->color_points[$current_index + 1]['g'];
                    $last_color_b           = $this->color_points[$current_index + 1]['b'];

                    $startpoint = $current_absolute_position;

                    $endpoint = $last_absolute_position;
                    $diff     = $this->color_points[$current_index + 1]['absolute'] - $this->color_points[$current_index]['absolute'];

                    $steps_r = ($last_color_r - $current_color_r ) / $diff;
                    $steps_g = ($last_color_g - $current_color_g ) / $diff;
                    $steps_b = ($last_color_b - $current_color_b) / $diff;

                    $init = 0;
                    while ($startpoint < $endpoint)
                    {
                        if ($init == 0)
                        {
                            $r = $current_color_r;
                            $g = $current_color_g;
                            $b = $current_color_b;
                        }
                        else
                        {
                            if ($steps_r > 0)
                            {
                                $r = $r + $steps_r;
                            }
                            else
                            {
                                $r = $r - abs($steps_r);
                            }

                            if ($steps_g > 0)
                            {
                                $g = $g + $steps_g;
                            }
                            else
                            {
                                $g = $g - abs($steps_g);
                            }

                            if ($steps_b > 0)
                            {
                                $b = $b + $steps_b;
                            }
                            else
                            {
                                $b = $b - abs($steps_b);
                            }
                        }

                        $f_r = intval($r);
                        $f_g = intval($g);
                        $f_b = intval($b);

                        $fill = imagecolorallocate($this->image, $f_r, $f_g, $f_b);
                        imageline($this->image, $startpoint, 0, $startpoint, $this->height, $fill);


                        $init = 1;
                        $startpoint++;
                    }
                }

                $current_index++;
            }
        }
        else
        {
            throw new \Exception('Fehler: Zu wenig vorhanden');
        }
    }

    /**
     * Bevor das Bild ausgegeben wird, kann mit draw_value() noch irgendeine
     * Stelle als schwarze vertikale Linie in die Grafik eingezeichnet werden.
     *
     * @return void
     */
    public function draw_value()
    {
        if ($this->value >= $this->min && $this->value <= $this->max)
        {
            $rel_position_of_value = 100 * ($this->value - $this->min) / ($this->max - $this->min);
            $abs_pos_of_value      = intval($this->width / 100 * $rel_position_of_value);


            $fill = imagecolorallocate($this->image, 0, 0, 0);

            imageline($this->image, $abs_pos_of_value, 0, $abs_pos_of_value, $this->height, $fill);
        }
    }

    /**
     * Der header für eine PNG-Datei wird gesendet und das Bild ausgegeben.
     *
     * @return void
     */
    public function draw()
    {

        header("Content-type: image/png");
        ImagePNG($this->image);
    }

    /**
     * Der Hexstring wird in die RGB-Darstellung überführt.
     *
     * @param string $hex Hexwert der Wunschfarbe
     * @return array
     */
    public function hex_to_rgb(string $hex)
    {

        if (strlen($hex) == 3)
        {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        }
        else
        {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }

        $rgb = ['r' => $r, 'g' => $g, 'b' => $b];

        return $rgb;
    }

    /**
     * Um die internen Daten jederzeit abrufen zu können (beispielsweise bei
     * Tests) kann man diese Funktion verwenden
     *
     * @return array
     */
    public function get_internal_data()
    {
        return [
            'min'            => $this->min,
            'max'            => $this->max,
            'value'          => $this->value,
            'width'          => $this->width,
            'height'         => $this->height,
            'colored_points' => $this->color_points,
            'image'          => $this->image
        ];
    }

}
