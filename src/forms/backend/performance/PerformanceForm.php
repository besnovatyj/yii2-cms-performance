<?php


/*
 * Copyright (c) 2026 Besnovatyj. Licensed under the MIT License.
 */

namespace Besnovatyj\Performance\forms\backend\performance;

use Besnovatyj\Forms\CompositeForm;
use Besnovatyj\Meta\MetaForm;
use Besnovatyj\Performance\entities\performance\Performance;

/**
 * @property MetaForm $meta
 * @property TaxonomiesForm $taxonomies
 * @property TagsForm $tags
 */
class PerformanceForm extends CompositeForm
{
    public string $title = '';
    public string $author = '';
    public string $genre = '';
    public string $description = '';
    public string $production_group = '';
    public string $actors = '';
    public string $age_limit = '';
    public string|null $premiere_date = null;
    public int|null $status = null;

    public function __construct(?Performance $performance = null, $config = [])
    {
        if ($performance) {
            $this->title = $performance->title;
            $this->author = $performance->author;
            $this->genre = $performance->genre;
            $this->description = $performance->description;
            $this->production_group = $performance->production_group;
            $this->actors = $performance->actors;
            $this->age_limit = $performance->age_limit;
            $this->premiere_date = $performance->premiere_date;
            $this->status = $performance->status;
            $this->meta = new MetaForm($performance->meta);
            $this->taxonomies = new TaxonomiesForm($performance);
            $this->tags = new TagsForm($performance);
        } else {
            $this->meta = new MetaForm();
            $this->taxonomies = new TaxonomiesForm();
            $this->tags = new TagsForm();
        }
        parent::__construct($config);
    }

    public function rules(): array
    {
        return [
            [['title', 'age_limit', 'status',], 'required'],
            [['title', 'author', 'genre', 'age_limit',], 'string', 'max' => 255],
            [['description', 'production_group', 'actors',], 'string'],
            ['premiere_date', 'date', 'format' => 'php:Y-m-d'],
            ['status', 'integer'],
            ['status', 'in', 'range' => [Performance::STATUS_DRAFT, Performance::STATUS_ACTIVE]],
        ];
    }

    protected function internalForms(): array
    {
        return ['meta', 'taxonomies', 'tags'];
    }

    public function statusList(): array
    {
        return [
            Performance::STATUS_DRAFT => 'DRAFT',
            Performance::STATUS_ACTIVE => 'ACTIVE',
        ];
    }
}
