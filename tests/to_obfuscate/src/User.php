<?php

/**
 * @author Pawel Maslak <pawel@maslak.it>
 */

namespace ExampleCompany;


class User
{
    public $table = 'users';

    public function process()
    {
        $var_x = 4;
        $var_x += 5;

        return $var_x;
    }

    public function save()
    {
        return false;
    }

    public function delete()
    {
    }

    public function validate($login)
    {
        if (!preg_match('/^[a-zA-Z0-9]{8-12}$/i', $login)) {
            return false;
        }

        return true;
    }

}
