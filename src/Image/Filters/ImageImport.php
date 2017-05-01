<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image\Filters;

use Jin2\Image\Image;

/**
 * Filtre image. Permet de coller sur l'image une autre image
 */
final class ImageImport extends AbstractFilter
{

  /**
   * @var Image  Image à coller
   */
  private $imagePasted;

  /**
   * @var resource  ImageResource GD sur laquelle appliquer le filtre
   */
  private $gdResource;

  /**
   * @var integer  Calage X
   */
  private $x;

  /**
   * @var integer  Calage Y
   */
  private $y;

  /**
   * Constructeur
   *
   * @param  string   $imagePath   Chemin de l'image à coller. (image ou imagePath requis)
   * @param  Image    $image       Objet image à coller. (image ou imagePath requis)
   * @param  resource $gdResource  ImageResource GD sur laquel appliquer le filtre
   * @param  integer  $x           Calage X du point supérieur gauche de l'image à coller.
   * @param  integer  $y           Calage Y du point supérieur gauche de l'image à coller.
   * @throws \Exception
   */
  public function __construct($imagePath = null, Image $image = null, $gdResource = null, $x, $y)
  {
    parent::__construct();

    $this->x = $x;
    $this->y = $y;

    if ($gdResource) {
      $this->gdResource = $gdResource;
    } else if($imagePath) {
      $this->imagePasted = new Image($imagePath);
    } else if($image) {
      $this->imagePasted = $image;
    } else {
      throw new \Exception('Vous devez fournir au filtre ImageImport le chemin d\'une image existante, un objet Jin2\Image\Image ou une ressource GD');
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
    if ($this->gdResource) {
      $source = $this->gdResource;
      $w = imagesx($this->gdResource);
      $h = imagesy($this->gdResource);
    } else {
      $source = $this->imagePasted->getImageRessource();
      $w = $this->imagePasted->getWidth();
      $h = $this->imagePasted->getHeight();
    }
    imagecopymerge($imageRessource, $source, $this->x, $this->y, 0, 0, $w, $h, 100);
    return $imageRessource;
  }

}
