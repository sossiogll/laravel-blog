<?php

use App\Models\Comment;
use App\Models\MediaLibrary;
use App\Models\Post;
use App\Models\Role;
use App\Models\Category;
use App\Models\Token;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Roles
        Role::firstOrCreate(['name' => Role::ROLE_EDITOR]);
        $role_admin = Role::firstOrCreate(['name' => Role::ROLE_ADMIN]);


        // MediaLibrary
        MediaLibrary::firstOrCreate([]);

        // Users
        $user = User::firstOrCreate(
            ['email' => 'sossiogll@010beautiful.life'],
            [
                'name' => 'sossiogll',
                'password' => Hash::make('sossiogll'),
                'email_verified_at' => now(),
                'authenticable' => true
            ]
        );

        $user->roles()->sync([$role_admin->id]);

        //Category

        $category = Category::firstOrCreate(
            [
                'name' => 'Test Category',
                'raw_custom_fields' => '{"custom_field_1":"Custom Field 1 Label", "custom_field_2":"Custom Fields 2 Label"}'
            ]
        );

        // Posts
        $post = Post::firstOrCreate(
            [
                'title' => 'Hello World',
                'author_id' => $user->id,
                'category_id' => $category->id
            ],
            [
                'posted_at' => now(),
                'content' => "
                    Welcome to Laravel-blog !<br><br>
                    Don't forget to read the README before starting.<br><br>
                    Feel free to add a star on Laravel-blog on Github !<br><br>
                    You can open an issue or (better) a PR if something went wrong."
            ]
        );

        $post->categories()->attach($category->id, ['raw_custom_fields_values' => '{"custom_field_1":"Custom Field 1 Value", "custom_field_2":"Custom Fields 2 Value"}']);

        // Comments
        Comment::firstOrCreate(
            [
                'author_id' => $user->id,
                'post_id' => $post->id
            ],
            [
                'posted_at' => now(),
                'content' => "Hey ! I'm a comment as example."
            ]
        );

        // API tokens
        User::where('api_token', null)->get()->each->update([
            'api_token' => Token::generate()
        ]);
    }
}
