<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image\Filters;

/**
 * Filtre image. Inverse les couleurs de l'image.
 */
final class Negate extends AbstractFilter
{

  /**
   * Constructeur
   */
  public function __construct() {
    parent::__construct();
  }

  /**
   * Application du filtre
   *
   * @param  resource $imageRessource  ImageRessource GD sur lequel appliquer le filtre
   * @return resource                  ImageRessource GD modifiée
   */
  public function apply($imageRessource)
  {
    imagefilter($imageRessource, IMG_FILTER_NEGATE);
    return $imageRessource;
  }
}
