<?php

namespace Drupal\commerce_product_review\Event;

use Drupal\commerce_product_review\Entity\ProductReviewInterface;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Defines the product review event.
 *
 * @see \Drupal\commerce_product_review\Event\ProductReviewEvents
 */
class ProductReviewEvent extends Event {

  /**
   * The product review.
   *
   * @var \Drupal\commerce_product_review\Entity\ProductReviewInterface
   */
  protected $review;

  /**
   * Constructs a new ProductReviewEvent.
   *
   * @param \Drupal\commerce_product_review\Entity\ProductReviewInterface $review
   *   The product.
   */
  public function __construct(ProductReviewInterface $review) {
    $this->review = $review;
  }

  /**
   * Gets the product review.
   *
   * @return \Drupal\commerce_product_review\Entity\ProductReviewInterface
   *   The product review.
   */
  public function getProductReview() {
    return $this->review;
  }

}
