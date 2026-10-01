<?php

/** for side bar menu active */
function set_active($route) {
    if (is_array($route)) {
        foreach ($route as $r) {
            if (request()->is($r)) return 'active';
        }
        return '';
    }
    return request()->is($route) ? 'active' : '';
}

/** for side bar menu show */
function set_show($route) {
    if (is_array($route)) {
        foreach ($route as $r) {
            if (request()->is($r)) return 'show';
        }
        return '';
    }
    return request()->is($route) ? 'show' : '';
}