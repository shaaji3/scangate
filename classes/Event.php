<?php

class Event {
    // Properties
    public $id;
    public $planner_id;
    public $title;
    public $description;
    public $location;
    public $date;
    public $banner;
    public $status;
    public $created_at;

    // Methods
    public function __construct($data = []) {
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->planner_id = $data['planner_id'] ?? null;
            $this->title = $data['title'] ?? null;
            $this->description = $data['description'] ?? null;
            $this->location = $data['location'] ?? null;
            $this->date = $data['date'] ?? null;
            $this->banner = $data['banner'] ?? null;
            $this->status = $data['status'] ?? 'draft';
        }
    }
}
