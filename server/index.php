<?php
error_reporting(0);
require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
// Please set to false in a production environment
$app['debug'] = true;

$data = json_decode(file_get_contents('../data/prices.json'));

$base_tarif = [
  '1' => 'Дневной',// (ГГ, РХ, Газ, Единый)
  '2' => 'Дневной с открытой датой', //  (РХ, Газ) (на Газпроме  берем тариф Дневной, т.к. на Газпроме все типы скипассов с открытой датой)
  '3' => 'Утренний', // (Газ)
  '4' => 'Полдня (с обеда)', // (ГГ, РХ, Газ)
  '5' => 'Вечерний', //  (ГГ, РХ, Газ)
  '6' => 'Весь день', //  (дневной + вечерний) (ГГ, РХ, Газ)- для розы хутор это будет цена дневного + Вечерний доплата
  '7' => 'Учебный', //  (ГГ, РХ)
  '8' => '2 дня подряд', //  (ГГ, РХ, Единый)
  '9' => '2 дня подряд с открытой датой', //  (РХ)
  '10' => '3 дня подряд', //  (ГГ, РХ, Газ, Единый) - Для газпрома берем тариф 3 дня в сезоне
  '11' => '3 дня подряд с открытой датой', //  (РХ)
  '12' => '3 дня в сезоне', //  (Газ)
  '13' => '3 дня в сезоне день+вечер', //  (Газ)
  '14' => '3 дня из 4', //  (РХ)
  '15' => '4 дня подряд', //  (Единый)
  '16' => '4 дня из 5', //  (РХ)
  '17' => '5 дней подряд', //  (Газ, Единый) - Для газпрома берем тариф 5 дней в сезоне
  '18' => '5 дней в сезоне', //  (Газ)
  '19' => '5 дней в сезоне день + вечер', // (Газ) 
  '20' => '5 дней из 6', //  (РХ)
  '21' => '6 дней подряд', //  (Единый)
  '22' => '6 дней из 8', //  (РХ)
  '23' => '7 дней подряд', //  (Газ, Единый) - Для газпрома берем тариф 7 дней в сезоне
  '24' => '7 дней в сезоне', //  (Газ)
  '25' => '7 дней в сезоне день +вечер', // (Газ)
  '26' => '7 дней из 9', //  (РХ)
  '27' => '8 дней подряд', // (Единый) 
  '28' => '8 дней из 11', //  (РХ)
  '29' => '10 дней из 14', //  (РХ)
  '30' => 'Сезонный', //  (ГГ, РХ, Газ, Единый)
  '31' => 'Прогулочный' //  (ГГ, РХ, Газ )- Для Розы Хутор это только Путь к вершинам, Для Горки Города - только Панорама, Для Газпрома - только Обзорный тур Лаура
];

$base_category = [
  '1'=>'0-6',
  '2'=>'7-14',
  '3'=>'15-60',
  '4'=>'61-70',
  '5'=>'71+',
  '6'=>'Льготный 1',
  '7'=>'Льготный 2',
  '8'=>'Семейный',
  '9'=>'Групповой',
  '10'=>'Студенческий',
  '11'=>'Учебный',
  //'12'=> 'Единый',
  //'13'=> 'Школьные группы'
];

$app->get('/', function() use ($data) {
    return json_encode($data);
});

$app->get('/table/{date}', function (Silex\Application $app, $date) use ($data, $base_tarif, $base_category) {
  $resp = (object) [];

  $prices = [];

  $price_cell = [];

  foreach( $data as $club=>$club_prices ){
    $day = str_replace('0','7',date('w',strtotime($date)));
    $active_period = 0;
    // detect period
    foreach ( $club_prices->period as $period_id => $period ){
      if( $period_id != 0 && $period->start <= $date && $period->end >= $date ){
        if ( isset($period->days) ){
          if( in_array($day,$period->days) ){
            $active_period = $period_id;
          }
        } else {
          $active_period = $period_id;
        }

      }
    }

//echo $active_period;

    $tarifs_acc = [];
    foreach($club_prices->tarif as $tarif_key => $tarif){
        $tarifs_acc[$tarif_key] = $tarif->acc;
        $base_tarif_note[$tarif->acc][$club] = $tarif->note;
    }




    foreach($club_prices->price as $price){

      if(
          (is_array($price->period) && in_array( $active_period, $price->period ) )
          || $price->period == 1000
          || $price->period == $active_period
        ){


          foreach( $club_prices->category as $category_cat => $category_inner_ids ){
            if($price->category == $category_cat){
              foreach( $category_inner_ids as $category_inner_id ){
                $price_cell[$club][$category_inner_id][$tarifs_acc[$price->tarif]] = $price->amount;
              }
            }
          }



      }
    }


  }


  foreach ($base_tarif as $key => $tarif_name) {
    foreach ($base_category as $key_cat => $category_name) {
      # code...
      $prices[$key][$key_cat] = [
        $category_name,
        //isset($price_cell['Роза Хутор'][$key_cat][$key]) ? $price_cell['Роза Хутор'][$key_cat][$key] : '-',
        //isset($price_cell['Горки Город'][$key_cat][$key]) ? $price_cell['Горки Город'][$key_cat][$key] : '-',
        //isset($price_cell['Газпром'][$key_cat][$key]) ? $price_cell['Газпром'][$key_cat][$key] : '-'
        isset($price_cell['rosaski'][$key_cat][$key]) ? $price_cell['rosaski'][$key_cat][$key] : '-',
        isset($price_cell['gorkigorod'][$key_cat][$key]) ? $price_cell['gorkigorod'][$key_cat][$key] : '-',
        isset($price_cell['gazprom'][$key_cat][$key]) ? $price_cell['gazprom'][$key_cat][$key] : '-',
        isset($price_cell['rosgaz'][$key_cat][$key]) ? $price_cell['rosgaz'][$key_cat][$key] : '-'
      ];
    }
  }


	$resp->prices = $prices;
  $resp->tarif = $base_tarif;
  $resp->tarif_notes = $base_tarif_note;
    return $app->json($resp);
});

/**
*
* ------------------------------------------
* ------------------------------------------
* ------------------------------------------
* ------------------------------------------
*
*/



$app->get('/table-2/{date}', function (Silex\Application $app, $date) use ($data, $base_tarif, $base_category) {
  $resp = (object) [];

  $prices = [];

  $price_cell = [];

  foreach( $data as $club=>$club_prices ){
    $day = str_replace('0','7',date('w',strtotime($date)));
    $active_period = 0;
    // detect period
    foreach ( $club_prices->period as $period_id => $period ){
      if( $period_id != 0 && $period->start <= $date && $period->end >= $date ){
        if ( isset($period->days) ){
          if( in_array($day,$period->days) ){
            $active_period = $period_id;
          }
        } else {
          $active_period = $period_id;
        }

      }
    }


    $tarifs_acc = [];
    foreach($club_prices->tarif as $tarif_key => $tarif){
        $tarifs_acc[$tarif_key] = $tarif->acc;
    }




    foreach($club_prices->price as $price){

      if(
        (is_array($price->period) && in_array( $active_period, $price->period ) )
        || $price->period == 1000
        || $price->period == $active_period
      ){


          foreach( $club_prices->category as $category_cat => $category_inner_ids ){
            if($price->category == $category_cat){
              foreach( $category_inner_ids as $category_inner_id ){
                $price_cell[$club][$category_inner_id][$tarifs_acc[$price->tarif]] = $price->amount;
              }
            }
          }



      }
    }


  }


  foreach ($base_category as $key_cat => $category_name) {
    foreach ($base_tarif as $key => $tarif_name) {
      # code...
      $prices[$key_cat][$key] = [
        $tarif_name,
        //isset($price_cell['Роза Хутор'][$key_cat][$key]) ? $price_cell['Роза Хутор'][$key_cat][$key] : '-',
        //isset($price_cell['Горки Город'][$key_cat][$key]) ? $price_cell['Горки Город'][$key_cat][$key] : '-',
        //isset($price_cell['Газпром'][$key_cat][$key]) ? $price_cell['Газпром'][$key_cat][$key] : '-'
        isset($price_cell['rosaski'][$key_cat][$key]) ? $price_cell['rosaski'][$key_cat][$key] : '-',
        isset($price_cell['gorkygorod'][$key_cat][$key]) ? $price_cell['gorkigorod'][$key_cat][$key] : '-',
        isset($price_cell['gazprom'][$key_cat][$key]) ? $price_cell['gzprom'][$key_cat][$key] : '-',
        isset($price_cell['rosgaz'][$key_cat][$key]) ? $price_cell['rosgaz'][$key_cat][$key] : '-'
      ];
    }
  }


	$resp->prices = $prices;
  $resp->tarif = $base_category;
    return $app->json($resp);
});

/**
*
* ------------------------------------------
* ------------------------------------------
* ------------------------------------------
* ------------------------------------------
*
*/

$app->get('/widget/{date}', function (Silex\Application $app, $date) use ($data) {

  $prices = [];

  foreach( $data as $club=>$club_prices ){
    $day = str_replace('0','7',date('w',strtotime($date)));
    $active_period = 0;
    // detect period
    foreach ( $club_prices->period as $period_id => $period ){
      if( $period_id != 0 && $period->start <= $date && $period->end >= $date ){
        if ( isset($period->days) ){
          if( in_array($day,$period->days) ){
            $active_period = $period_id;
          }
        } else {
          $active_period = $period_id;
        }

      }
    }


    // traverce prices
/*
    echo $club.'
    ';
    echo $active_period.'
    ';
*/
    $tarif_day = 0;
    foreach($club_prices->tarif as $tarif_key => $tarif){
      if( $tarif->acc == '1' ){
        $tarif_day = $tarif_key;
      }
    }
  /*  echo $tarif_day.'
    ';*/

    $price_cell = [];
    foreach($club_prices->price as $price){

      if( (is_array($price->period) && in_array( $active_period, $price->period ) ) || $price->period == $active_period ){


        if($price->tarif == $tarif_day){

          if( $price->category == 'adult' ){
            if( isset($price_cell[$club]['adult']) && $price_cell[$club]['adult'] > $price->amount && $price->amount != '-' ){
              $price_cell[$club]['adult'] = $price->amount;
            }else{
              $price_cell[$club]['adult'] = $price->amount;
            }
          }
          if( $price->category == 'child' ){
            if( isset($price_cell[$club]['child']) && $price_cell[$club]['child'] > $price->amount && $price->amount != '-' ){
              $price_cell[$club]['child'] = $price->amount;
            }else{
              $price_cell[$club]['child'] = $price->amount;
            }
          }

        }


      }
    }


    $adult_price = isset($price_cell[$club]['adult']) ? $price_cell[$club]['adult'] : ' - ';
    $child_price = isset($price_cell[$club]['child']) ? $price_cell[$club]['child'] : ' - ';
    $prices[] = [$club,$adult_price,$child_price];
  }



    return $app->json($prices);
});

$app->run();
