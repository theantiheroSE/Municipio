<?php

namespace Municipio\Controller\Navigation\Decorators\Breadcrumb;

use Municipio\Controller\Navigation\Config\NewMenuConfigInterface;
use Municipio\Controller\Navigation\NewMenuInterface;
use Municipio\Helper\CurrentPostId;
use WpService\Contracts\GetPostType;
use WpService\Contracts\GetPostTypeObject;

class AppendArchiveMenuItem implements NewMenuInterface
{
    public function __construct(private NewMenuInterface $inner, private GetPostType&GetPostTypeObject $wpService)
    {
    }

    public function getMenuItems(): array
    {
        $menuItems          = $this->inner->getMenuItems();

        $postType           = $this->wpService->getPostType(CurrentPostId::get());
        $archiveLink        = get_post_type_archive_link($postType);

        if ($archiveLink) {
            $defaultLabel = __("Untitled page", 'municipio');
            
            if (is_archive()) {
                $label = get_queried_object()->label ?? $defaultLabel;
            } else {
                $label = $this->wpService->getPostTypeObject($postType)->label ?? $defaultLabel;
            }

            $menuItems[] = [
                'label'   => __($label),
                'href'    => $archiveLink,
                'current' => false,
                'icon'    => 'chevron_right'
            ];
        }

        return $menuItems;
    }

    public function getConfig(): NewMenuConfigInterface
    {
        return $this->inner->getConfig();
    }
}