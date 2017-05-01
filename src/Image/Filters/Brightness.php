<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image\Filters;

/**
 * Filtre image. Modifie la luminosité d'une image.
 */
final class Brightness extends AbstractFilter
{

  /**
   * @var integer Valeur de luminosité (de -255 à 255).
   */
  private $brightness;

  /**
   * Constructeur
   *
   * @param integer $brightness   Valeur de luminosité. de -255 à 255. 0 = pas de modification
   */
  public function __construct($brightness)
  {
    parent::__construct();
    $this->brightness = $brightness;
  }

  /**
   * Application du filtre
   *
   * @param  resource $imageRessource  ImageRessource GD sur lequel appliquer le filtre
   * @return resource                  ImageRessource GD modifiée
   */
  public function apply($imageRessource)
  {
    imagefilter ($imageRessource , IMG_FILTER_BRIGHTNESS, $this->brightness);
    return $imageRessource;
  }

}