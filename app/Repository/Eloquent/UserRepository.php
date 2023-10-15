<?php
declare(strict_types=1);

namespace App\Repository\Eloquent;

use App\Models\User;
use App\Repository\Contracts\UserRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    protected $user;

    /**
     * Construct
     *
     * @param \App\Models\User  $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * retrieves all user registers
     *
     * @param $attributes
     * @return Array
     */
    public function findAll(): array
    {
        return $this->user->get()->toArray();
    }

    /**
     * create a new user
     *
     * @param $data
     * @return Array
     */
    public function create($data): object
    {
        return $this->user->create($data);
    }

    /**
     * update a user
     *
     * @param $email
     * @param $data
     * @return Array
     */
    public function update(string $email,array $data): object
    {
        $user = $this->user->find($email);
        $user->update($data);

        $user->refresh();

        return $user;
    }

    /**
     * delete a user
     *
     * @param $email
     * @return Array
     */
    public function delete(string $email): bool
    {
        if(!$user = $this->user->find($email)){
            throw new Exception("User Not Found", 1);
        }

        return $user->delete();
    }

    /**
     * select a user by documentNumber
     *
     * @param $documentNumber
     * @return Array
     */
    public function find(string $id): ?object
    {
        return $this->user->where('id',$id)->first();
    }

    /**
     * select a user by id
     *
     * @param $id
     * @return Collection
     */
    public function findById($id): Collection
    {
        $user = $this->user->find($id);
        if(!$user){
            throw new Exception("User Not Found", 1);
        }

        return $user;
    }
    
    /**
     * returns registers with re Attributes
     *
     * @param $attributes
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function selectRelationAtribbutes($attributes) : Object
    {
       return $this->user->with($attributes)->get();
    }
}
