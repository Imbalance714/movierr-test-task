<?php

use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categories')->insert([
            [
                'name' => 'Action',
            ],
            [
                'name' => 'Adventure',
            ],
            [
                'name' => 'Animation',
            ],
            [
                'name' => 'Biography',
            ],
            [
                'name' => 'Comedy',
            ],
            [
                'name' => 'Crime',
            ],
            [
                'name' => 'Documentary',
            ],
            [
                'name' => 'Drama',
            ],
            [
                'name' => 'Family',
            ],
            [
                'name' => 'Fantasy',
            ],
            [
                'name' => 'Film-Noir',
            ],
            [
                'name' => 'Game-Show',
            ],
            [
                'name' => 'History',
            ],
            [
                'name' => 'Horror',
            ],
            [
                'name' => 'Music',
            ],
            [
                'name' => 'Musical',
            ],
            [
                'name' => 'Mystery',
            ],
            [
                'name' => 'News',
            ],
            [
                'name' => 'Reality-TV',
            ],
            [
                'name' => 'Romance',
            ],
            [
                'name' => 'Sci-Fi',
            ],
            [
                'name' => 'Sport',
            ],
            [
                'name' => 'Talk-Show',
            ],
            [
                'name' => 'Thriller',
            ],
            [
                'name' => 'War',
            ],
            [
                'name' => 'Western',
            ]
        ]);
    }
}
