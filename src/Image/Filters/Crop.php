<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image\Filters;

use Jin2\Image\Image;

/**
 * Filtre image. Permet de croper une image sur des dimensions spécifiées
 * Cette méthode entraîne une perte de matière.
 */
final class Crop extends AbstractFilter
{

  /**
   * @var integer Largeur souhaitée
   */
  private $width;

  /**
   * @var integer Hauteur souhaitée
   */
  private $height;

  /**
   * Alignement vertical TOP
   */
  const VERTICAL_TOP = 0;

  /**
   * Alignement vertical Centre
   */
  const VERTICAL_CENTER = 1;

  /**
   * Alignement vertical Bas
   */
  const VERTICAL_BOTTOM = 2;

  /**
   * Alignement horizontal gauche
   */
  const HORIZONTAL_LEFT = 3;

  /**
   * Alignement horizontal centré
   */
  const HORIZONTAL_CENTER = 4;

  /**
   * Alignement horizontal droite
   */
  const HORIZONTAL_RIGHT = 5;

  /**
   * Position verticale de l'image croppée
   *
   * @var int
   */
  private $verticalPosition;

  /**
   * Position horizontale de l'image croppée
   *
   * @var int
   */
  private $horizontalPosition;

  /**
   * Constructeur
   *
   * @param integer $width                Largeur de l'image en sortie
   * @param integer $height               Hauteur de l'image en sortie
   * @param integer $verticalPosition     Position verticale de l'image croppée/ (Crop::VERTICAL_CENTER par défaut)
   * @param integer $horizontalPosition   Position horizontale de l'image croppée/ (Crop::HORIZONTAL_CENTER par défaut)
   */
  public function __construct($width, $height, $verticalPosition = self::VERTICAL_CENTER, $horizontalPosition = self::HORIZONTAL_CENTER)
  {
    parent::__construct();
    $this->width = $width;
    $this->height = $height;

    if ($verticalPosition != self::VERTICAL_BOTTOM
      && $verticalPosition != self::VERTICAL_CENTER
      && $verticalPosition != self::VERTICAL_TOP) {
      throw new \Exception('Position verticale non valide.');
    }

    if ($horizontalPosition != self::HORIZONTAL_CENTER
      && $horizontalPosition != self::HORIZONTAL_LEFT
      && $horizontalPosition != self::HORIZONTAL_RIGHT) {
      throw new \Exception('Position horizontale non valide.');
    }

    $this->verticalPosition = $verticalPosition;
    $this->horizontalPosition = $horizontalPosition;
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

    // On effectue au préalable un resize
    $xratio = $startWidth / $this->width;
    $yratio = $startHeight / $this->height;
    if ($xratio == $yratio) {
      if ($startHeight == $this->height && $startWidth == $this->width) {
        return $imageRessource;
      }
      $newWidth  = $this->width;
      $newHeight = $this->height;
    } else if($xratio > $yratio) {
      $newHeight = $this->height;
      $newWidth  = $startWidth * $newHeight / $startHeight;
    } else {
      $newWidth  = $this->width;
      $newHeight = $startHeight * $newWidth / $startWidth;
    }

    if (isset($newHeight)) {
      $resizedTmp = Image::createEmptyContainer($newWidth, $newHeight);
      imagecopyresampled($resizedTmp, $imageRessource, 0, 0, 0, 0, $newWidth, $newHeight, $startWidth, $startHeight);
    }
    imagedestroy($imageRessource);

    // On coupe les cotés ou le haut
    $startWidth = imagesx($resizedTmp);
    $startHeight = imagesy($resizedTmp);

    if ($startWidth == $this->width && $startHeight == $this->height) {

      // Cas particulier, aucune opération complémentaire nécessaire
      return $resizedTmp;

    } else if ($startWidth == $this->width) {

      // On a redimensionné sur la largeur. On coupe le haut et le bas
      $resized = Image::createEmptyContainer($this->width, $this->height);
      $xdecay = 0;
      $ydecay = null;

      if($this->verticalPosition == self::VERTICAL_TOP){
        $ydecay = 0;
      } else if ($this->verticalPosition == self::VERTICAL_BOTTOM) {
        $ydecay = $startHeight - $this->height;
      } else if ($this->verticalPosition == self::VERTICAL_CENTER) {
        $ydecay = ($startHeight - $this->height) / 2;
      }

      imagecopyresampled($resized, $resizedTmp, 0, 0, $xdecay, $ydecay, $this->width, $this->height, $this->width, $this->height);
      imagedestroy($resizedTmp);
      return $resized;

    } else {

      // On a redimensionné sur la hauteur. On coupe à droite et à gauche
      $resized = Image::createEmptyContainer($this->width, $this->height);
      $xdecay = null;
      $ydecay = 0;

      if($this->horizontalPosition == self::HORIZONTAL_LEFT){
        $xdecay = 0;
      }else if($this->horizontalPosition == self::HORIZONTAL_RIGHT){
        $xdecay = $startWidth - $this->width;
      }else if($this->horizontalPosition == self::HORIZONTAL_CENTER){
        $xdecay = ($startWidth - $this->width) / 2;
      }

      imagecopyresampled($resized, $resizedTmp, 0, 0, $xdecay, $ydecay, $this->width, $this->height, $this->width, $this->height);
      imagedestroy($resizedTmp);
      return $resized;

    }
  }

}
