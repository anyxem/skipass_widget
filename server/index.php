<?php
error_reporting(0);
require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
// Please set to false in a production environment
$app['debug'] = true;

$data = json_decode(file_get_contents('../data/prices.json'));

$base_tarif = [
  '1'=>'Утренний',
  '2'=>'Дневной',
  '3'=>'Полуденный',
  '4'=>'Вечерний',
  '5'=>'Дневной+вечерний',
  '6'=>'2 дня подряд',
  '7'=>'3 дня подряд',
  '8'=>'4 дня подряд',
  '9'=>'5 дней подряд',
  '10'=>'3 дня из 4',
  '11'=>'6 дней из 8',
  '12'=>'8 дней из 11',
  '13'=>'10 дней из 14',
  '14'=>'3 дня в сезоне',
  '15'=>'5 дней в сезоне',
  '16'=>'7 дней в сезоне',
  '17'=>'все выходные',
  '18'=>'все будни'
];

$base_category = [
  '1'=>'0-5',
  '2'=>'6',
  '3'=>'7-14',
  '4'=>'15-16',
  '5'=>'17',
  '6'=>'18-60',
  '7'=>'61-65',
  '8'=>'66-70',
  '9'=>'71+',
  '10'=>'Студенческий',
  '11'=>'Льготный',
  '12'=>'Семейный',
  '13'=>'Групповой 3',
  '14'=>'Групповой 4',
  '15'=>'Групповой 5'
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
      if( $tarif->acc == '2' ){
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
