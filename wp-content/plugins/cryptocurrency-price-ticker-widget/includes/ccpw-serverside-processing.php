<?php
/**
 * Create response for datatable AJAX request
 */


function ccpw_get_ajax_data(){

		$start_point    = $_REQUEST['start']?$_REQUEST['start']:0;
        $data_length    = $_REQUEST['length']?$_REQUEST['length']:10;
        $current_page   = (int)$_REQUEST['draw']?$_REQUEST['draw']:1;
        $requiredCurrencies = ccpw_set_default_if_empty($_REQUEST['requiredCurrencies'],'top-10');
        $fiat_currency = $_REQUEST['currency'] ? $_REQUEST['currency'] :'USD';
        $fiat_currency_rate = $_REQUEST['currencyRate'] ? $_REQUEST['currencyRate'] : 1;
        $coin_no=$start_point+1;
        $coins_list=array();
        $order_col_name = 'id';
        $order_type ='ASC';
        $DB = new ccpw_database;
        $Total_DBRecords = '1000';
        $coins_request_count=$data_length+$start_point;
   
        switch($requiredCurrencies){
            case 'top-10':
                $requiredCurrencies='10';
            break;
            case 'top-50':
                $requiredCurrencies='50';
            break;
            case 'top-100':
                $requiredCurrencies='100';
            break;
            case 'all':
                $requiredCurrencies = $Total_DBRecords;
            break;
        }

        $coindata= $DB->get_coins( array("number"=>$data_length,'offset'=> $start_point,'orderby' => $order_col_name,
        'order' => $order_type
          ));
          $coin_ids=array();
          if($coindata){
            foreach($coindata as $coin){
                 $coin_ids[]= $coin->coin_id;
            }
        }
   
		$response = array();
        $coins = array();
        $bitcoin_price = get_transient('ccpw_btc_price');
        $coins_list=array();
       
        if($coindata){

            foreach($coindata as $coin){
                $coin = (array)$coin;
                $coins['rank'] = $coin_no;
                $coins['id']    =   $coin['coin_id'];
                if( ccpw_get_coin_logo($coin['coin_id'], $size = 32)==false){
                    $coins['logo'] ='<img  alt="'.$coin['name'].'" src="'.$coin['logo'].'">';
                  }else{
                    $coins['logo'] = ccpw_get_coin_logo( $coin['coin_id'] );
                  }
                $coins['symbol']= strtoupper($coin['symbol']);
                $coins['name'] = strtoupper($coin['name']);
                $coins['price'] = $coin['price'];
                if($fiat_currency=="USD"){
                    $coins['price'] = $coin['price'];
                    $coins['market_cap'] = $coin['market_cap'];
                    $coins['total_volume'] = $coin['total_volume'];
                    $c_price=$coin['price'];
                }else{
                    $coins['price'] = $coin['price']* $fiat_currency_rate;
                    $coins['market_cap'] = $coin['market_cap'] * $fiat_currency_rate;
                    $coins['total_volume'] = $coin['total_volume'] * $fiat_currency_rate;
                }
                $coins['change_percentage_24h'] = number_format($coin['percent_change_24h'],2,'.','');
                $coins['market_cap'] = $coin['market_cap'];
                $coins['total_volume'] = $coin['total_volume'];
                $coins['supply'] = $coin['circulating_supply'];

                $coin_no++;
                $coins_list[]= $coins;

            }   //end of foreach-block
        }   //end of if-block
       
		$response = array("draw"=>$current_page,"recordsTotal"=>$Total_DBRecords,"recordsFiltered"=> $requiredCurrencies,"data"=>$coins_list);
		echo json_encode( $response );
}