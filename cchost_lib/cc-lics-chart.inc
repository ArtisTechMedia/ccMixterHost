<?
/*
* Creative Commons has made the contents of this file
* available under a CC-GNU-GPL license:
*
* http://creativecommons.org/licenses/GPL/2.0/
*
* A copy of the full license can be found as part of this
* distribution in the file LICENSE.TXT.
* 
* You may use the ccHost software in accordance with the
* terms of that license. You agree that you are solely 
* responsible for your use of the ccHost software and you
* represent and warrant to Creative Commons that your use
* of the ccHost software will comply with the CC-GNU-GPL.
*
* $Id: cc-lics-chart.inc 12720 2009-06-04 08:23:02Z fourstones $
*
*/

function & cc_license_chart()
{
    static $chart;
    
    if( !isset($chart) )
    {
        $chart = array
          (
            'nc-sampling+' => array
                (
                    'nc-sampling+' =>   'nc-sampling+', 
                    'sampling+' =>   'nc-sampling+', 
                    'sampling' =>   'nc-sampling+', 
                    'share-alike_3' =>   'nc-sampling+', 
                    'by-nc-sa_3' =>   'nc-sampling+', 
                    'noncommercial_3' =>   'nc-sampling+', 
                    'attribution_3' =>   'nc-sampling+', 
                    'publicdomain' =>   'nc-sampling+', 
                    'cczero' =>   'nc-sampling+', 
                ),

            'sampling+' =>  array
                (
                    'nc-sampling+' =>   'nc-sampling+', 
                    'sampling+' =>   'sampling+', 
                    'sampling' =>   'sampling+', 
                    'share-alike_3' =>   'sampling+', 
                    'by-nc-sa_3' =>   'nc-sampling+', 
                    'noncommercial_3' =>  'nc-sampling+' , 
                    'attribution_3' =>   'sampling+', 
                    'publicdomain' =>   'sampling+', 
                    'cczero' =>   'sampling+', 
                ),

            'sampling' =>   array
                (
                    'nc-sampling+' =>  'nc-sampling+' , 
                    'sampling+' =>   'nc-sampling+', 
                    'sampling' =>   'sampling', 
                    'share-alike_3' =>   'sampling', 
                    'by-nc-sa_3' =>  'nc-sampling+' , 
                    'noncommercial_3' =>   'nc-sampling+', 
                    'attribution_3' =>   'sampling', 
                    'publicdomain' =>   'sampling', 
                    'cczero' =>   'sampling', 
                ),

            'share-alike_3' =>   array
                (
                    'nc-sampling+' =>  'nc-sampling+'  , 
                    'sampling+' =>   'sampling+' , 
                    'sampling' =>   'sampling' , 
                    'share-alike_3' =>   'share-alike_3', 
                    'by-nc-sa_3' =>   'by-nc-sa_3', 
                    'noncommercial_3' =>  'by-nc-sa_3' , 
                    'attribution_3' =>   'share-alike_3', 
                    'publicdomain' =>  'share-alike_3' , 
                    'cczero' =>   'share-alike_3', 
                ),

            'by-nc-sa_3' =>   array
                (
                    'nc-sampling+' =>  'nc-sampling+' , 
                    'sampling+' =>   'nc-sampling+', 
                    'sampling' =>   'nc-sampling+', 
                    'share-alike_3' =>  'by-nc-sa_3', 
                    'by-nc-sa_3' =>   'by-nc-sa_3', 
                    'noncommercial_3' =>  'by-nc-sa_3' , 
                    'attribution_3' =>  'by-nc-sa_3' , 
                    'publicdomain' =>   'by-nc-sa_3', 
                    'cczero' =>   'by-nc-sa_3', 
                ),

            'noncommercial_3' =>   array
                (
                    'nc-sampling+' =>   'nc-sampling+' , 
                    'sampling+' =>   'nc-sampling+' , 
                    'sampling' =>   'nc-sampling+' , 
                    'share-alike_3' =>  'by-nc-sa_3'  , 
                    'by-nc-sa_3' =>  'by-nc-sa_3'  , 
                    'noncommercial_3' =>  'noncommercial_3' , 
                    'attribution_3' =>   'noncommercial_3', 
                    'publicdomain' =>   'noncommercial_3', 
                    'cczero' =>   'noncommercial_3', 
                ),

            'attribution_3' =>   array
                (
                    'nc-sampling+' =>  'nc-sampling+' , 
                    'sampling+' =>   'sampling+', 
                    'sampling' =>   'sampling' , 
                    'share-alike_3' =>  'share-alike_3'  , 
                    'by-nc-sa_3' =>  'by-nc-sa_3'  , 
                    'noncommercial_3' =>  'noncommercial_3' , 
                    'attribution_3' =>   'attribution_3', 
                    'publicdomain' =>   'attribution_3', 
                    'cczero' =>   'attribution_3', 
                ),

            'publicdomain' =>   array
                (
                    'nc-sampling+' =>  'nc-sampling+' , 
                    'sampling+' =>   'sampling+', 
                    'sampling' =>   'sampling' , 
                    'share-alike_3' =>  'share-alike_3'  , 
                    'by-nc-sa_3' =>  'by-nc-sa_3'  , 
                    'noncommercial_3' =>  'noncommercial_3' , 
                    'attribution_3' =>   'attribution_3', 
                    'publicdomain' =>   'publicdomain' , 
                    'cczero' =>   'cczero', 
                ),

            'cczero' =>   array
                (
                    'nc-sampling+' =>  'nc-sampling+' , 
                    'sampling+' =>   'sampling+', 
                    'sampling' =>   'sampling' , 
                    'share-alike_3' =>  'share-alike_3'  , 
                    'by-nc-sa_3' =>  'by-nc-sa_3'  , 
                    'noncommercial_3' =>  'noncommercial_3' , 
                    'attribution_3' =>   'attribution_3', 
                    'publicdomain' =>   'cc_zero' , 
                    'cczero' =>   'cczero', 
                ),

            );
    }

    return $chart;
}

function cc_stricter_license( $lic1,$lic2 )
{
    $update = array
        (
            'share-alike' =>    'share-alike_3'  , 
            'by-nc-sa' =>       'by-nc-sa_3'  , 
            'noncommercial' =>  'noncommercial_3' , 
            'attribution' =>    'attribution_3',
            'cc_zero'     =>    'cczero'
        );


    if( array_key_exists($lic1,$update) )
        $lic1 = $update[$lic1];
    if( array_key_exists($lic2,$update) )
        $lic2 = $update[$lic2];

    $chart = function_exists('custom_lic_chart') ? $custom_lic_chart() : cc_license_chart();
    if( !empty($chart[$lic1][$lic2]) )
        return $chart[$lic1][$lic2];
    return null;
    //trigger_error("invalid lic mix $lic1 - $lic2");
}

?>
