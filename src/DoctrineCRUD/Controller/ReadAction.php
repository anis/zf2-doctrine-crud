<?php
/**
 * Read action
 *
 * Provides methods to read the entries of a specific entity with a paging feature.
 * This trait is expected to be used by \DoctrineCRUD\Controller\CRUDController.
 *
 * PHP Version 5.4.13
 *
 * @author  Anis Safine <anis@safine.me>
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
namespace DoctrineCRUD\Controller;

/**
 * Read action
 *
 * @author  Anis Safine <anis@safine.me>
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */
trait ReadAction
{
    /**
     * Number of entities per page
     *
     * Zero or lower to deactivate paging (every entities will be displayed)
     *
     * @var integer
     */
    protected $entitiesPerPage = 10;

    /**
     * Paging template
     *
     * @var string
     */
    protected $pagingTemplate = 'doctrine-crud/crud/read/paging';

    /**
     * Read action
     *
     * Retrieves and passes to the view the appropriate entities, depending on the paging configuration.
     *
     * @return array Variables to be passed to the view :
     *               "currentPage" integer Index of current page (starting from 1)
     *               "totalPages"  integer Total number of pages
     *               "fields"      array   List of formatted field names
     *               "entities"    array   List of retrieves entities
     */
    public function readAction()
    {
        // Determine the total number of pages
        $fields = $this->displayableFields();
        $countRequest = 'SELECT COUNT(e.'.$fields[0].') FROM '.$this->getEntityClass().' e';
        $count = $this->getEM()->createQuery($countRequest)->getSingleScalarResult();

        if ($this->entitiesPerPage > 0 && $count > 0) {
            $totalPages = intval(ceil($count / $this->entitiesPerPage));
        } else {
            $totalPages = 1;
        }

        // Determine which page to display
        $currentPage = $this->requestedPage();
        if ($currentPage === null) {
            $currentPage = 1;
        } elseif ($currentPage < 1) {
            $currentPage = 1;
        } elseif ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }

        // Finally, determine the appropriate limit and offset
        if ($this->entitiesPerPage > 0) {
            $limit = $this->entitiesPerPage;
        } else {
            $limit = $count;
        }

        $offset = ($currentPage - 1) * $limit;

        // Determine the sorting parameters
        $requested = $this->requestedSorting();
        $sorting = array();
        if ($requested !== null && in_array($requested['by'], $this->sortableFields())) {
            $sorting[$requested['by']] = $requested['order'];
        }

        // Collect the data into a simple array
        $entities = $this->getEM()->getRepository($this->getEntityClass())->findBy(
            array(),
            $sorting,
            $limit,
            $offset
        );

        $data = array();
        foreach ($entities as $entity) {
            $row = array();
            foreach ($fields as $fieldName) {
                $row[$fieldName] = call_user_func(array($entity, 'get'.$fieldName));
            }

            array_push($data, $row);
        }

        // Return the data to the view
        return array(
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'fields'   => array_combine($fields, array_map(array($this, 'formatName'), $fields)),
            'sortableFields' => $this->sortableFields(),
            'currentSorting' => $sorting,
            'entities' => $data,
            'pagingTemplate' => 'doctrine-crud/crud/read/paging',
        );
    }

    /**
     * Gets the ordered list of fields to be displayed
     *
     * If not overriden, this method will return all fields that are not associations, identifiers first.
     *
     * @return array The name of all displayable fields, in the order they should be displayed
     *
     * @todo cache the result to improve execution performances
     */
    protected function displayableFields()
    {
        $fields = array();

        $metadata = $this->getEM()->getClassMetadata($this->getEntityClass());
        foreach ($metadata->getFieldNames() as $fieldName) {
            // Exclude association fields
            if ($metadata->hasAssociation($fieldName)) {
                continue;
            }

            // Put identifiers on top of the list
            if ($metadata->isIdentifier($fieldName)) {
                array_unshift($fields, $fieldName);
            } else {
                array_push($fields, $fieldName);
            }
        }

        return $fields;
    }

    /**
     * Gets the list of fields usable for sorting
     *
     * If not overriden, this method will return all displayable fields
     *
     * @return array The name of all sortable fields
     */
    protected function sortableFields()
    {
        return $this->displayableFields();
    }

    /**
     * Gets the index of the requested page (starting from 1)
     *
     * If not overriden, this method will check for a variable named "page" in the "route", "query" and "post"
     * parameters (and in that exact order).
     *
     * @return integer|null Index of the requested page if any, null otherwise
     */
    protected function requestedPage()
    {
        // Is it in the route?
        $page = intval($this->params()->fromRoute('page', null));
        if ($page > 0) {
            return $page;
        }

        // Is it in the query?
        $page = intval($this->params()->fromQuery('page', null));
        if ($page > 0) {
            return $page;
        }

        // Is it posted?
        $page = intval($this->params()->fromPost('page', null));
        if ($page > 0) {
            return $page;
        }

        // No page was requested
        return null;
    }

    /**
     * Gets the field and order of the requested sorting (if any)
     *
     * If not overriden, this method will check for two variables named "sortBy" and "sortOrder" in the "route",
     * "query", and "post" parameters (in that exact order).
     * The variables "sortBy" should contain a field name, and "sortOrder" should contain either "asc" or "desc"
     *
     * @return array|null An array with two keys "by" and "order" if any sorting was requested, any otherwise
     */
    protected function requestedSorting()
    {
        $sort = array('by' => null, 'order' => null);

        // Is it in the route?
        $sort['by'] = $this->params()->fromRoute('sortBy', null);
        $sort['order'] = trim(strtolower($this->params()->fromRoute('sortOrder', null)));
        if (!in_array($sort['order'], array('asc', 'desc'))) {
            $sort['order'] = null;
        }

        if ($sort['by'] !== null && $sort['order'] !== null) {
            return $sort;
        }

        // Is it in the query?
        if ($sort['by'] === null) {
            $sort['by'] = $this->params()->fromQuery('sortBy', null);
        }

        if ($sort['order'] === null) {
            $sort['order'] = trim(strtolower($this->params()->fromQuery('sortOrder', null)));

            if (!in_array($sort['order'], array('asc', 'desc'))) {
                $sort['order'] = null;
            }
        }

        if ($sort['by'] !== null && $sort['order'] !== null) {
            return $sort;
        }

        // Is it posted?
        if ($sort['by'] === null) {
            $sort['by'] = $this->params()->fromPost('sortBy', null);
        }

        if ($sort['order'] === null) {
            $sort['order'] = strtolower(trim($this->params()->fromPost('sortOrder', null)));

            if (!in_array($sort['order'], array('asc', 'desc'))) {
                $sort['order'] = null;
            }
        }

        if ($sort['by'] !== null && $sort['order'] !== null) {
            return $sort;
        }

        // No sorting was requested
        return null;
    }
}
