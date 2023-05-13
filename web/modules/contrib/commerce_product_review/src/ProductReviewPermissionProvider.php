<?php

namespace Drupal\commerce_product_review;

use Drupal\entity\EntityPermissionProvider;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Provides permissions for Product Reviews.
 */
class ProductReviewPermissionProvider extends EntityPermissionProvider {

  /**
   * {@inheritdoc}
   */
  public function buildPermissions(EntityTypeInterface $entity_type) {
    $permissions = parent::buildPermissions($entity_type);
    $permissions['publish commerce_product_review'] = [
      // Cast to string, as TranslatableMarkup objects don't sort properly.
      'title' => (string) $this->t('Publish product review'),
      'description' => $this->t('Publish a product review without admin approval.'),
      'provider' => $entity_type->getProvider(),
    ];
    return $permissions;
  }

}
