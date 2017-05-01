<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image\Filters;

/**
 * Filtre image. Applique un effet de pixelisation.
 */
final class Pixelate extends AbstractFilter
{

  /**
   * @var integer Taille des pixels
   */
  private $pixelSize;

  /**
   * Constructeur
   *
   * @param integer $pixelSize   Taille des pixels
   */
  public function __construct($pixelSize)
  {
    parent::__construct();
    $this->pixelSize = $pixelSize;
  }

  /**
   * Application du filtre
   *
   * @param  resource $imageRessource  ImageRessource GD sur lequel appliquer le filtre
   * @return resource                  ImageRessource GD modifiée
   */
  public function apply($imageRessource)
  {
    imagefilter($imageRessource , IMG_FILTER_PIXELATE, $this->pixelSize, true);
    return $imageRessource;
  }

}
