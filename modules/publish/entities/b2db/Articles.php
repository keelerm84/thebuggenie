<?php

    namespace thebuggenie\modules\publish\entities\b2db;

    use b2db\Criteria,
        TBGContext, TBGProject, TBGProjectsTable,
        thebuggenie\modules\publish\entities\Article;

    /**
     * @method \thebuggenie\modules\publish\entities\b2db\Articles getTable() Retrieves an instance of this table
     * @method \thebuggenie\modules\publish\entities\Article selectById(integer $id) Retrieves an article
     *
     * @Table(name="articles")
     * @Entity(class="\thebuggenie\modules\publish\entities\Article")
     */
    class Articles extends \TBGB2DBTable
    {

        const B2DB_TABLE_VERSION = 2;
        const B2DBNAME = 'articles';
        const ID = 'articles.id';
        const NAME = 'articles.name';
        const CONTENT = 'articles.content';
        const IS_PUBLISHED = 'articles.is_published';
        const DATE = 'articles.date';
        const AUTHOR = 'articles.author';
        const SCOPE = 'articles.scope';

        public function _setupIndexes()
        {
            $this->_addIndex('name_scope', array(self::NAME, self::SCOPE));
        }

        public function getAllArticles()
        {
            $crit = $this->getCriteria();
            $crit->addWhere(self::SCOPE, TBGContext::getScope()->getID());
            $crit->addOrderBy(self::NAME);

            return $this->select($crit);
        }

        public function getManualSidebarArticles(TBGProject $project = null, $filter = null)
        {
            $crit = $this->getCriteria();
            $crit->addWhere(self::SCOPE, TBGContext::getScope()->getID());
            $crit->addWhere('articles.article_type', Article::TYPE_MANUAL);
            $crit->addWhere('articles.name', '%' . strtolower($filter) . '%', Criteria::DB_LIKE);
            if ($project instanceof TBGProject)
            {
                $ctn = $crit->returnCriterion(self::NAME, ucfirst($project->getKey()) . ":%", Criteria::DB_LIKE);
                $ctn->addOr(self::NAME, "Category:" . ucfirst($project->getKey()) . "%", Criteria::DB_LIKE);
                $crit->addWhere($ctn);
            }
            else
            {
                foreach (TBGProjectsTable::getTable()->getAllIncludingDeleted() as $project)
                {
                    $crit->addWhere(self::NAME, "Category:" . ucfirst($project->getKey()) . "%", Criteria::DB_NOT_LIKE);
                    $crit->addWhere(self::NAME, ucfirst($project->getKey()) . ":%", Criteria::DB_NOT_LIKE);
                }
            }

            $crit->addOrderBy(self::NAME, 'asc');

            $articles = $this->select($crit);
            foreach ($articles as $i => $article)
            {
                if (!$article->hasAccess())
                    unset($articles[$i]);
            }

            return $articles;
        }

        public function getManualSidebarCategories(TBGProject $project = null)
        {
            $crit = $this->getCriteria();
            $crit->addWhere(self::SCOPE, TBGContext::getScope()->getID());
            $crit->addWhere('articles.article_type', Article::TYPE_MANUAL);
            $crit->addWhere('articles.parent_article_id', 0);
            if ($project instanceof TBGProject)
            {
                $crit->addWhere(self::NAME, "Category:" . ucfirst($project->getKey()) . "%", Criteria::DB_LIKE);
            }
            else
            {
                foreach (TBGProjectsTable::getTable()->getAllIncludingDeleted() as $project)
                {
                    $crit->addWhere(self::NAME, "Category:" . ucfirst($project->getKey()) . "%", Criteria::DB_NOT_LIKE);
                    $crit->addWhere(self::NAME, ucfirst($project->getKey()) . ":%", Criteria::DB_NOT_LIKE);
                }
            }

            $crit->addOrderBy(self::NAME, 'asc');

            $articles = $this->select($crit);
            foreach ($articles as $i => $article)
            {
                if (!$article->hasAccess())
                    unset($articles[$i]);
            }

            return $articles;
        }

        public function getArticles(TBGProject $project = null, $limit = 10)
        {
            $crit = $this->getCriteria();
            $crit->addWhere(self::SCOPE, TBGContext::getScope()->getID());
            $crit->addWhere('articles.article_type', Article::TYPE_WIKI);

            if ($project instanceof TBGProject)
            {
                $crit->addWhere(self::NAME, "Category:" . ucfirst($project->getKey()) . "%", Criteria::DB_LIKE);
                $crit->addOr(self::NAME, ucfirst($project->getKey()) . ":%", Criteria::DB_LIKE);
            }
            else
            {
                foreach (TBGProjectsTable::getTable()->getAllIncludingDeleted() as $project)
                {
                    $crit->addWhere(self::NAME, "Category:" . ucfirst($project->getKey()) . "%", Criteria::DB_NOT_LIKE);
                    $crit->addWhere(self::NAME, ucfirst($project->getKey()) . ":%", Criteria::DB_NOT_LIKE);
                }
            }

            $crit->addOrderBy(self::DATE, 'desc');

            $articles = $this->select($crit);
            foreach ($articles as $id => $article)
            {
                if (!$article->hasAccess())
                    unset($articles[$id]);
            }

            return $articles;
        }

        public function getArticleByName($name)
        {
            $crit = $this->getCriteria();
            $crit->addWhere(self::NAME, $name);
            $crit->addWhere(self::SCOPE, TBGContext::getScope()->getID());
            return $this->selectOne($crit, 'none');
        }

        public function doesArticleExist($name)
        {
            $crit = $this->getCriteria();
            $crit->addWhere(self::NAME, $name);
            $crit->addWhere(self::SCOPE, TBGContext::getScope()->getID());

            return (bool) $this->doCount($crit);
        }

        public function deleteArticleByName($name)
        {
            $crit = $this->getCriteria();
            $crit->addWhere(self::NAME, $name);
            $crit->addWhere(self::SCOPE, TBGContext::getScope()->getID());
            $crit->setLimit(1);
            $row = $this->doDelete($crit);

            return $row;
        }

        public function getUnpublishedArticlesByUser($user_id)
        {
            $crit = $this->getCriteria();
            $crit->addWhere(self::IS_PUBLISHED, false);
            $crit->addWhere(self::SCOPE, TBGContext::getScope()->getID());

            $res = $this->select($crit);

            return $res;
        }

        public function doesNameConflictExist($name, $id, $scope = null)
        {
            $scope = ($scope === null) ? TBGContext::getScope()->getID() : $scope;

            $crit = $this->getCriteria();
            $crit->addWhere(self::NAME, $name);
            $crit->addWhere(self::ID, $id, Criteria::DB_NOT_EQUALS);
            $crit->addWhere(self::SCOPE, $scope);

            return (bool) $this->doCount($crit);
        }

        public function findArticlesContaining($content, $project = null, $limit = 5, $offset = 0)
        {
            $crit = $this->getCriteria();
            if ($project instanceof TBGProject)
            {
                $ctn = $crit->returnCriterion(self::NAME, "%{$content}%", Criteria::DB_LIKE);
                $ctn->addWhere(self::NAME, "category:" . $project->getKey() . "%", Criteria::DB_LIKE);
                $crit->addWhere($ctn);

                $ctn = $crit->returnCriterion(self::NAME, "%{$content}%", Criteria::DB_LIKE);
                $ctn->addWhere(self::NAME, $project->getKey() . "%", Criteria::DB_LIKE);
                $crit->addOr($ctn);

                $ctn = $crit->returnCriterion(self::CONTENT, "%{$content}%", Criteria::DB_LIKE);
                $ctn->addWhere(self::NAME, $project->getKey() . "%", Criteria::DB_LIKE);
                $crit->addOr($ctn);
            }
            else
            {
                $crit->addWhere(self::NAME, "%{$content}%", Criteria::DB_LIKE);
                $crit->addOr(self::CONTENT, "%{$content}%", Criteria::DB_LIKE);
            }

            $resultcount = $this->doCount($crit);

            if ($resultcount)
            {
                $crit->setLimit($limit);

                if ($offset)
                    $crit->setOffset($offset);

                return array($resultcount, $this->doSelect($crit));
            }
            else
            {
                return array($resultcount, array());
            }
        }

        public function save($name, $content, $published, $author, $id = null, $scope = null)
        {
            $scope = ($scope !== null) ? $scope : TBGContext::getScope()->getID();
            $crit = $this->getCriteria();
            if ($id == null)
            {
                $crit->addInsert(self::NAME, $name);
                $crit->addInsert(self::CONTENT, $content);
                $crit->addInsert(self::IS_PUBLISHED, (bool) $published);
                $crit->addInsert(self::AUTHOR, $author);
                $crit->addInsert(self::DATE, NOW);
                $crit->addInsert(self::SCOPE, $scope);
                $res = $this->doInsert($crit);
                return $res->getInsertID();
            }
            else
            {
                $crit->addUpdate(self::NAME, $name);
                $crit->addUpdate(self::CONTENT, $content);
                $crit->addUpdate(self::IS_PUBLISHED, (bool) $published);
                $crit->addUpdate(self::AUTHOR, $author);
                $crit->addUpdate(self::DATE, NOW);
                $res = $this->doUpdateById($crit, $id);
                return $res;
            }
        }

        public function getDeadEndArticles(TBGProject $project = null)
        {
            $names = ArticleLinks::getTable()->getUniqueArticleNames();

            $crit = $this->getCriteria();
            if ($project instanceof TBGProject)
            {
                $crit->addWhere(self::NAME, ucfirst($project->getKey()) . ":%", Criteria::DB_LIKE);
            }
            else
            {
                foreach (TBGProjectsTable::getTable()->getAllIncludingDeleted() as $project)
                {
                    if (trim($project->getKey()) == '')
                        continue;
                    $crit->addWhere(self::NAME, "Category:" . ucfirst($project->getKey()) . "%", Criteria::DB_NOT_LIKE);
                    $crit->addWhere(self::NAME, ucfirst($project->getKey()) . ":%", Criteria::DB_NOT_LIKE);
                }
            }
            $crit->addWhere(self::NAME, $names, Criteria::DB_NOT_IN);
            $crit->addWhere(self::CONTENT, '#REDIRECT%', Criteria::DB_NOT_LIKE);
            $crit->addWhere(self::SCOPE, TBGContext::getScope()->getID());

            return $this->select($crit);
        }

        public function getAllByLinksToArticleName($article_name)
        {
            $names_res = ArticleLinks::getTable()->getLinkingArticles($article_name);
            if (empty($names_res))
                return array();

            $names = array();
            while ($row = $names_res->getNextRow())
            {
                $names[] = $row[ArticleLinks::ARTICLE_NAME];
            }

            $crit = $this->getCriteria();
            $crit->addWhere(self::NAME, $names, Criteria::DB_IN);
            $crit->addWhere(self::SCOPE, TBGContext::getScope()->getID());

            return $this->select($crit);
        }

        public function getUnlinkedArticles(TBGProject $project = null)
        {
            $names = ArticleLinks::getTable()->getUniqueLinkedArticleNames();

            $crit = $this->getCriteria();
            if ($project instanceof TBGProject)
            {
                $crit->addWhere(self::NAME, ucfirst($project->getKey()) . ":%", Criteria::DB_LIKE);
            }
            else
            {
                foreach (TBGProjectsTable::getTable()->getAllIncludingDeleted() as $project)
                {
                    if (trim($project->getKey()) == '')
                        continue;
                    $crit->addWhere(self::NAME, "Category:" . ucfirst($project->getKey()) . "%", Criteria::DB_NOT_LIKE);
                    $crit->addWhere(self::NAME, ucfirst($project->getKey()) . ":%", Criteria::DB_NOT_LIKE);
                }
            }
            $crit->addWhere(self::NAME, $names, Criteria::DB_NOT_IN);
            $crit->addWhere(self::CONTENT, '#REDIRECT%', Criteria::DB_NOT_LIKE);
            $crit->addWhere(self::SCOPE, TBGContext::getScope()->getID());

            return $this->select($crit);
        }

        public function getUncategorizedArticles(TBGProject $project = null)
        {
            $crit = $this->getCriteria();
            if ($project instanceof TBGProject)
            {
                $crit->addWhere(self::NAME, ucfirst($project->getKey()) . ":%", Criteria::DB_LIKE);
            }
            else
            {
                foreach (TBGProjectsTable::getTable()->getAllIncludingDeleted() as $project)
                {
                    if (trim($project->getKey()) == '')
                        continue;
                    $crit->addWhere(self::NAME, "Category:" . ucfirst($project->getKey()) . "%", Criteria::DB_NOT_LIKE);
                    $crit->addWhere(self::NAME, ucfirst($project->getKey()) . ":%", Criteria::DB_NOT_LIKE);
                }
            }
            $crit->addWhere(self::NAME, "Category:%", Criteria::DB_NOT_LIKE);
            $crit->addWhere(self::CONTENT, '#REDIRECT%', Criteria::DB_NOT_LIKE);
            $crit->addWhere(self::CONTENT, '%[Category:%', Criteria::DB_NOT_LIKE);
            $crit->addWhere(self::SCOPE, TBGContext::getScope()->getID());

            return $this->select($crit);
        }

        public function getUncategorizedCategories(TBGProject $project = null)
        {
            $crit = $this->getCriteria();
            if ($project instanceof TBGProject)
            {
                $crit->addWhere(self::NAME, "Category:" . ucfirst($project->getKey()) . ":%", Criteria::DB_LIKE);
            }
            else
            {
                foreach (TBGProjectsTable::getTable()->getAllIncludingDeleted() as $project)
                {
                    if (trim($project->getKey()) == '')
                        continue;
                    $crit->addWhere(self::NAME, "Category:" . ucfirst($project->getKey()) . "%", Criteria::DB_NOT_LIKE);
                    $crit->addWhere(self::NAME, ucfirst($project->getKey()) . ":%", Criteria::DB_NOT_LIKE);
                }
            }
            $crit->addWhere(self::CONTENT, '#REDIRECT%', Criteria::DB_NOT_LIKE);
            $crit->addWhere(self::SCOPE, TBGContext::getScope()->getID());

            return $this->select($crit);
        }

        public function getAllArticlesSpecial(TBGProject $project = null)
        {
            $crit = $this->getCriteria();
            if ($project instanceof TBGProject)
            {
                $crit->addWhere(self::NAME, "Category:" . ucfirst($project->getKey()) . ":%", Criteria::DB_NOT_LIKE);
                $crit->addWhere(self::NAME, ucfirst($project->getKey()) . ":%", Criteria::DB_LIKE);
            }
            else
            {
                foreach (TBGProjectsTable::getTable()->getAllIncludingDeleted() as $project)
                {
                    if (trim($project->getKey()) == '')
                        continue;
                    $crit->addWhere(self::NAME, "Category:" . ucfirst($project->getKey()) . "%", Criteria::DB_NOT_LIKE);
                    $crit->addWhere(self::NAME, ucfirst($project->getKey()) . ":%", Criteria::DB_NOT_LIKE);
                }
            }
            $crit->addWhere(self::SCOPE, TBGContext::getScope()->getID());

            return $this->select($crit);
        }

        protected function _getAllInNamespace($namespace, TBGProject $project = null)
        {
            $crit = $this->getCriteria();
            if ($project instanceof TBGProject)
            {
                $crit->addWhere(self::NAME, "{$namespace}:" . ucfirst($project->getKey()) . ":%", Criteria::DB_LIKE);
            }
            else
            {
                $crit->addWhere(self::NAME, "{$namespace}:%", Criteria::DB_LIKE);
                foreach (TBGProjectsTable::getTable()->getAllIncludingDeleted() as $project)
                {
                    if (trim($project->getKey()) == '')
                        continue;
                    $crit->addWhere(self::NAME, "{$namespace}:" . ucfirst($project->getKey()) . "%", Criteria::DB_NOT_LIKE);
                    $crit->addWhere(self::NAME, ucfirst($project->getKey()) . ":%", Criteria::DB_NOT_LIKE);
                }
            }
            $crit->addWhere(self::SCOPE, TBGContext::getScope()->getID());

            return $this->select($crit);
        }

        public function getAllCategories(TBGProject $project = null)
        {
            return $this->_getAllInNamespace('Category', $project);
        }

        public function getAllTemplates(TBGProject $project = null)
        {
            return $this->_getAllInNamespace('Template', $project);
        }

    }
