<?php

namespace Tests\Integration\Adapter\Out\Persistence\Repository;

use App\Adapter\Out\Persistence\Repository\UserRepository;
use App\Domain\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\PostgresIntegrationTestCase;

class UserRepositoryTest extends PostgresIntegrationTestCase
{
    use RefreshDatabase;

    private UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->repository = $this->app->make(UserRepository::class);
    }

    public function test_should_save_and_find_user_by_id(): void
    {
        $userDomain = new User(
            id: 123456789012345678, 
            name: 'john_doe',
            fullName: 'John Doe',
            email: 'john@example.com',
            password: bcrypt('secret123'),
            status: 'ACTIVE',
            emailVerifiedAt: new \DateTimeImmutable(),
            lastLoginAt: null,
            version: 1
        );

        $savedUser = $this->repository->save($userDomain);

        $foundUser = $this->repository->findById(123456789012345678);

        $this->assertNotNull($foundUser);
        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($userDomain->getId(), $foundUser->getId());
        $this->assertEquals('john@example.com', $foundUser->getEmail());
        $this->assertEquals('John Doe', $foundUser->getFullName());
    }
}