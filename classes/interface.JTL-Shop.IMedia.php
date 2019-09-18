<?php

/**
 * Interface IMedia
 */
interface IMedia
{
    /**
     * @param string $request
     * @return mixed
     */
    public function isValid($request);

    /**
     * @param string $request
     * @return mixed
     */
    public function handle($request);
}
