<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image\Filters;

use Jin2\Image\Image;
use Jin2\Image\ImagickGd;

/**
 * Filtre image. Permet de modifier l'opactité d'une image PNG (uniquement !)
 */
final class Opacity extends AbstractFilter
{

  /**
   * @var integer  Degré d'opacité
   */
  private $opacity;

  /**
   * Constructeur
   *
   * @param integer $opacity  Opacité. (de 0 à 100)
   */
  public function __construct($opacity)
  {
    parent::__construct();
    $this->opacity = $opacity;
  }

  /**
   * Application du filtre
   *
   * @param  resource $imageRessource  ImageRessource GD sur lequel appliquer le filtre
   * @return resource                  ImageRessource GD modifiée
   * @throws \Exception
   */
  public function apply($imageRessource)
  {
    try {
      $imagick = ImagickGd::convertGDRessourceToImagick($imageRessource, Image::IMAGE_TYPE_PNG);
      $imagick->evaluateImage(\Imagick::EVALUATE_MULTIPLY, $this->opacity / 100, \Imagick::CHANNEL_ALPHA);
      $gd = ImagickGd::convertImagickToGDRessource($imagick, Image::IMAGE_TYPE_PNG);
      return $gd;
    } catch (\Exception $e) {
      throw new \Exception('Le filtre Opacity n\'est appliquable que sur des images PNG');
    }
    return null;
  }

}
