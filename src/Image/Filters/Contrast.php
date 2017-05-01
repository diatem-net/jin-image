<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image\Filters;

/**
 * Filtre image. Modifie le contraste d'une image.
 */
final class Contrast extends AbstractFilter
{

  /**
   * @var integer Intensité du contraste
   */
  private $contrast;

  /**
   * Constructeur
   * @param integer $contrast   Degré de contraste. 0 = valeur de départ
   */
  public function __construct($contrast)
  {
    parent::__construct();
    $this->contrast = $contrast;
  }

  /**
   * Application du filtre
   *
   * @param  resource $imageRessource  ImageRessource GD sur lequel appliquer le filtre
   * @return resource                  ImageRessource GD modifiée
   */
  public function apply($imageRessource)
  {
    imagefilter($imageRessource, IMG_FILTER_CONTRAST, $this->contrast);
    return $imageRessource;
  }

}
