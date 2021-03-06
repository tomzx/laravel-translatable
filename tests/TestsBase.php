<?php

use Orchestra\Testbench\TestCase;
use Dimsav\Translatable\Test\Model\Country;

class TestsBase extends TestCase {

    protected $queriesCount;

    public function setUp()
    {
        parent::setUp();

        App::register('Dimsav\Translatable\TranslatableServiceProvider');

        $this->resetDatabase();
        $this->countQueries();
    }

    public function testRunningMigration()
    {
        $country = Country::find(1);
        $this->assertEquals('gr', $country->iso);
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['path.base'] = __DIR__ . '/../vendor/orchestra/testbench/src/fixture';
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql', array(
            'driver'   => 'mysql',
            'host' => 'localhost',
            'database' => 'translatable_test',
            'username' => 'homestead',
            'password' => 'secret',
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
        ));
        $app['config']->set('translatable::locales', array('el', 'en', 'fr', 'de', 'id'));
    }

    protected function getPackageAliases()
    {
        return array('Eloquent' => 'Illuminate\Database\Eloquent\Model');
    }

    protected function countQueries() {
        $that = $this;
        $event = App::make('events');
        $event->listen('illuminate.query', function() use ($that) {
            $that->queriesCount++;
        });
    }

    private function resetDatabase()
    {
        // Relative to the testbench app folder: vendors/orchestra/testbench/src/fixture
        $migrationsPath = '../../../../../tests/migrations';
        $artisan = $this->app->make('Illuminate\Contracts\Console\Kernel');

        // Makes sure the migrations table is created
        $artisan->call('migrate', [
            '--database' => 'mysql',
            '--path'     => $migrationsPath,
        ]);

        // We empty all tables
        $artisan->call('migrate:reset', [
            '--database' => 'mysql',
        ]);

        // Migrate
        $artisan->call('migrate', [
            '--database' => 'mysql',
            '--path'     => $migrationsPath,
        ]);
    }
}