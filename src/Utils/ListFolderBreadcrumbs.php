<?php

namespace App\Utils;

use App\Entity\Portal;
use App\Repository\FolderRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class ListFolderBreadcrumbs
 * @package App\Utils
 */
class ListFolderBreadcrumbs
{

    use StringHelper;

    /**
     * @var array
     */
    private $pagination = [];

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * @var FolderRepository
     */
    private $folderRepository;

    /**
     * FolderPagination constructor.
     * @param UrlGeneratorInterface $router
     * @param FolderRepository $folderRepository
     */
    public function __construct(UrlGeneratorInterface $router, FolderRepository $folderRepository)
    {
        $this->router = $router;
        $this->folderRepository = $folderRepository;
    }


    /**]
     * @param $folderId
     * @param Portal $portal
     * @param string $route
     * @return string
     */
    public function generate($folderId, Portal $portal, $route = 'list_settings') {

        $allFoldersUrl = sprintf('<a href="%s/folders">All folders ></a>', $this->router->generate(
            $route,
            ['internalIdentifier' => $portal->getInternalIdentifier()]
        ));

        if(!$folderId) {
            return sprintf("<span>%s</span>", $allFoldersUrl);
        }

        $this->recursion($folderId, $portal, $route);

        $this->pagination = array_reverse($this->pagination);

        array_unshift($this->pagination, $allFoldersUrl);

        return $this->str_lreplace(">", "",sprintf("<span>%s</span>", implode(" ", $this->pagination)));

    }

    /**
     * @param $folderId
     * @param Portal $portal
     * @param $route
     */
    private function recursion($folderId, Portal $portal, $route) {

        $folder = $this->folderRepository->find($folderId);

        if(count($folder->getChildFolders()) === 0) {
            $link = '<a href="%s/folders/%s">%s</a>';
        } else {
            $link = '<a href="%s/folders/%s">%s ></a>';
        }

        $this->pagination[] = sprintf($link, $this->router->generate(
            $route,
            ['internalIdentifier' => $portal->getInternalIdentifier()]
        ), $folder->getId(), $folder->getName());

        if($parentFolder = $folder->getParentFolder()) {

            $this->recursion($parentFolder->getId(), $portal, $route);
        }

    }

}