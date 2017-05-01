<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image\Filters;

use Jin2\Image\Image;

/**
 * Filtre image. Permet de redimentionner une image sans déformation.
 * Selon la taille de l'image initiale, la largeur ou la hauteur est prise
 * comme valeur de référence. L'autre dimension sera moindre que celle souhaitée
 * de manière à conserver la cohérence de l'image, sans perte de matière.
 */
final class Resize extends AbstractFilter
{

  /**
   * @var integer Largeur maximale souhaitée
   */
  private $width;

  /**
   * @var integer Hauteur maximale souhaitée
   */
  private $height;

  /**
   * Constructeur
   *
   * @param integer $width   Largeur maximale souhaitée
   * @param integer $height  Hauteur maximale souhaitée
   */
  public function __construct($width, $height)
  {
    parent::__construct();
    $this->width = $width;
    $this->height = $height;
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
    $yratio = $startHeight / $this->height;
    if ($xratio == $yratio) {
      if ($startHeight == $this->height && $startWidth == $this->width) {
        return $imageRessource;
      }
      $newWidth  = $this->width;
      $newHeight = $this->height;
    } else if($xratio > $yratio) {
      $newWidth  = $this->width;
      $newHeight = $startHeight * $newWidth / $startWidth;
    } else {
      $newHeight = $this->height;
      $newWidth  = $startWidth * $newHeight / $startHeight;
    }

    $resized = Image::createEmptyContainer($newWidth, $newHeight);
    imagecopyresampled($resized, $imageRessource, 0, 0, 0, 0, $newWidth, $newHeight, $startWidth, $startHeight);
    imagedestroy($imageRessource);
    return $resized;
  }

}
