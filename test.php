<?php

    /**
     *  Пробуем  выполнить запросы к авито и спарсить объявления
     * 
     */

    require __DIR__ . "/vendor/autoload.php";

    /**
     * Удаляет префикс html классов
     */
    function clearHtmlClasses($html) {
        return preg_replace_callback(

            '/class="([\w\-\_ ]+)"/', 

            function($match) {   
                $classNames = explode(' ', $match[1]); 
                $newClassValue = ""; 
                foreach($classNames as $name) {
                    $newClassValue .= substr($name, 0, strlen($name) - 6)." ";   
                }        
                return 'class="'.$newClassValue.'"';
            },

            $html
        );
    }


    function initialize() {
        $city = "moskva";
        $query = "гитара";
        $category = "muzykalnye_instrumenty";
        $page = 2;

        $client = new GuzzleHttp\Client([
            'base_uri' => 'https://avito.ru'
        ]);

        $response = $client->request('GET', "/$city/$category", [
            'query' => "q=$query&p=$page"
        ]); 

        $html = clearHtmlClasses($response->getBody());

        $dom = new PHPHtmlParser\Dom;
        $dom->loadStr($html);
        $items = $dom->find('.iva-item-root.photo-slider-slider.iva-item-list');      // Парсим все объявления (контейнеры)

        $info = [];
        foreach($items as $item) {
            $info []= getItemInfo($item);
        }

        dump($info); 
    }

    class AdModel {
        public 
            $id,
            $title,
            $link,
            $price,
            $city,
            $time;


            function __construct($id, $title, $link, $price = null, $city = null, $time = null) {
                
                if(!$title || !$link) 
                    throw "Failed to parse adv";
                
                $time = $time ?? "not stated";
                $city = $city ?? "not stated";
                $price = $price ?? "not stated";

                $this->id = $id;
                $this->title = $title;
                $this->link = $link;
                $this->price = $price;
                $this->city = $city;
                $this->time = $time;
            }

            /*
            * Serialize to JSON
            *
            */
            function getJSON() {
                return json_encode([
                    "title" => $this->title,
                    "link" => $this->link,
                    "price" => $this->price,
                    "city" => $this->city,
                    "time" => $this->time,
                ]);
            }
    }
    
    /**
     * Получаем свойство объекта и если, его нет, возвращаем null
     */
    function getProp($obj, $prop) {
        try{
            return $obj->{$prop};
        }
        catch(Exception $e) {
            return null;
        }
    }

    /**
     * Get data from item
     * 
     */
    function getItemInfo($item) {
        try{
            $model = new AdModel(          
                getProp($item, 'data-item-id'),
                getProp($item->find("h3.title-root.iva-item-title"),"text"),
                getProp($item->find(".iva-item-sliderLink"), "href"),
                getProp($item->find(".price-text"), "text"),
                null, //$item->find(".geo-address span")->text,
                getProp($item->find("[data-marker=item-date]"), "text")
            );
        }        
        catch(Exception $e) {
            echo $e;
            dump($item);
        }
        return $model;
    }


    initialize();

    