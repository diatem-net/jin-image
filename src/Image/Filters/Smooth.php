<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image\Filters;

/**
 * Filtre image. Adoucit les contours.
 */
final class Smooth extends AbstractFilter
{

  /**
   * @var integer Intensité. 0 = intensité max.
   */
  private $intensity;

  /**
   * Constructeur
   *
   * @param integer $intensity   Degré de lissage 0 = intensité maximale
   */
  public function __construct($intensity)
  {
    parent::__construct();
    $this->intensity = $intensity;
  }

  /**
   * Application du filtre
   *
   * @param  resource $imageRessource  ImageRessource GD sur lequel appliquer le filtre
   * @return resource                  ImageRessource GD modifiée
   */
  public function apply($imageRessource)
  {
    imagefilter($imageRessource, IMG_FILTER_SMOOTH, $this->intensity);
    return $imageRessource;
  }

}
