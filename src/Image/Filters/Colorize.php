<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image\Filters;

/**
 * Filtre image. Applique un effet de colorisation à l'image.
 */
final class Colorize extends AbstractFilter
{

  /**
   * @var integer Composante rouge de l'effet
   */
  private $red;

  /**
   * @var integer Composant verte de l'effet
   */
  private $green;

  /**
   * @var integer Composante bleue de l'effet
   */
  private $blue;

  /**
   * @var integer Alpha de l'effet
   */
  private $alpha;

  /**
   * Constructeur
   *
   * @param integer $red        Composante rouge de la couleur à appliquer. De 0 à 255
   * @param integer $green      Composante verte de la couleur à appliquer. De 0 à 255
   * @param integer $blue       Composante bleue de la couleur à appliquer. De 0 à 255
   * @param integer $intensity  Intensité de l'effet. De 0 à 100.
   */
  public function __construct($red, $green, $blue, $intensity = 100)
  {
    parent::__construct();
    $this->red = $red;
    $this->green = $green;
    $this->blue = $blue;
    $this->alpha = 100 - $intensity;
  }

  /**
   * Application du filtre
   *
   * @param  resource $imageRessource  ImageRessource GD sur lequel appliquer le filtre
   * @return resource                  ImageRessource GD modifiée
   */
  public function apply($imageRessource)
  {
    imagefilter ($imageRessource , IMG_FILTER_COLORIZE, $this->red, $this->green, $this->blue, $this->alpha);
    return $imageRessource;
  }

}
