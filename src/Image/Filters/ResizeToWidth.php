<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image\Filters;

use Jin2\Image\Image;

/**
 * Filtre image. Permet de redimentionner une image sans déformation en spécifiant une largeur fixe de sortie. La hauteur sera variable
 */
final class ResizeToWidth extends AbstractFilter
{

  /**
   * @var integer Largeur souhaitée
   */
  private $width;

  /**
   * Constructeur
   *
   * @param integer $width  Largeur souhaitée
   */
  public function __construct($width)
  {
    parent::__construct();
    $this->width = $width;
  }

  /**
   * Application du filtre
   *
   * @param  resource $imageRessource  ImageRessource GD sur lequel appliquer le filtre
   * @return resource                  ImageRessource GD modifiée
   */
  public function apply($imageRessource)
  {
    $startWidth = imagesx($imageRessource);
    $startHeight = imagesy($imageRessource);

    $xratio = $startWidth / $this->width;

    $newWidth  = $this->width;
    $newHeight = $startHeight * $newWidth / $startWidth;

    $resized = Image::createEmptyContainer($newWidth, $newHeight);
    imagecopyresampled($resized, $imageRessource, 0, 0, 0, 0, $newWidth, $newHeight, $startWidth, $startHeight);
    imagedestroy($imageRessource);
    return $resized;
  }

}
