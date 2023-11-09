<?php

$rawMenus = file_get_contents("menus.json");

$menus = json_decode($rawMenus);

$structuredMenus = [
    "custom" => []
];

foreach ($menus->custom as $menu)
{
    if($menu->type == "parent")
    {
        array_push($structuredMenus["custom"], $menu);
    }else{
        if($menu->parent_id)
        {
            foreach ($structuredMenus["custom"] as $key => $newMenu) {
                if($menu->parent_id == $newMenu->id)
                {
                    $newMenu->data[] = $menu;
                }
            }

            continue;
        }

        array_push($structuredMenus, $menu);
    }
}

print_r($structuredMenus);