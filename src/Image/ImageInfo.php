<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Image;

/**
 * Classe permettant de récupérer des informations sur une image
 */
class ImageInfo
{

  /**
   * @var Image  Image
   */
  protected $image;

  public function __construct($imagePath)
  {
    $this->image = new \Imagick($imagePath);
  }

  public function getFullInfos()
  {
    $datas = $this->image->getImageProperties();
    return $datas;
  }

  public function getWidth()
  {
    return $this->image->getimagewidth();
  }

  public function getHeight()
  {
    return $this->image->getimageheight();
  }

}
