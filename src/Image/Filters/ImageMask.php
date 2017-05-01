<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image\Filters;

use Jin2\Image\Image;
use Jin2\Image\ImagickGd;

/**
 * Filtre image. Applique un masque sur l'image à partir d'une image en niveaux de gris.
 */
final class ImageMask extends AbstractFilter
{

  /**
   * @var \Imagick  Objet Imagick du masque
   */
  private $mask;

  /**
   * Constructeur
   *
   * @param string $maskFilePath    Chemin absolu ou relatif de l'image servant de masque.
   */
  public function __construct($maskFilePath = null, $imgRessource = null)
  {
    parent::__construct();
    if (!$maskFilePath && !$imgRessource) {
      throw new \Exception('Vous devez spécifier maskFilePath ou imgRessource');
    }
    if ($maskFilePath) {
      $this->mask = new \Imagick($maskFilePath);
    } else if($imgRessource) {
      $this->mask = ImagickGd::convertGDRessourceToImagick($imgRessource);
    }
  }

  /**
   * Application du filtre
   *
   * @param  resource $imageRessource  ImageRessource GD sur lequel appliquer le filtre
   * @return resource                  ImageRessource GD modifiée
   */
  public function apply($imageRessource)
  {
    if ($this->image->getExtension() != Image::IMAGE_TYPE_PNG) {
      throw new \Exception('Le filtre ImageMask n\'est appliquable que sur des images PNG');
    }

    $image = ImagickGd::convertGDRessourceToImagick($imageRessource);
    $image->setImageMatte(0);
    $image->compositeImage($this->mask, \Imagick::COMPOSITE_COPYOPACITY, 0, 0, \Imagick::CHANNEL_ALL);

    $gd = ImagickGd::convertImagickToGDRessource($image, true);

    return $gd;
  }

}
