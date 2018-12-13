<?php
/**
 * Created by PhpStorm.
 * User: Julio
 * Date: 13/12/2018
 * Time: 11:28
 */

class Extras
{
    protected $conection;
    /**
     * Extras constructor.
     */
    public function __construct($conection)
    {
        $this->conection = $conection;
    }

    public function sendNumber() {
        //$file_name = 'C:/Users/Julio/Documents/theWinningNumber.xml';
        //$file_name = 'http://www.flalottery.com/video/en/theWinningNumber.xml';
        $file_name = "http://www.flalottery.com/video/en/theWinningNumber.xml";

        echo("Archivo: ".$file_name);
        echo(" </br> ");
        echo(" </br> ");

        $html = \Sunra\PhpSimple\HtmlDomParser::file_get_html($file_name);
        //$html = HtmlDomParser::file_get_html($file_name, false, null, 0);

        $htmlCode = $html->find('item[game=pick3]');

        echo("Code: ".$htmlCode[0]->plaintext);
        echo(" </br> ");
        echo(" </br> ");

        $dias_semana_esp = ["Domingo", "Lunes", "Martes", "Miércoles", "Jueves", "Viernes", "Sábado"];
        $meses_esp = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        $fechaToday = \Carbon\Carbon::now('America/Havana');

        $fecha = $htmlCode[0]->plaintext;

        $pos = strpos($fecha,"winning numbers are ");
        $date = substr($fecha,$pos+37,10);
        $date = strtotime($date);
        $date = date('d/m/Y', $date);
        $mediodia_fecha = $date;

        $numero = substr($fecha,$pos+20,5);
        $mediodia_centena = substr($numero, 0, 1);
        $mediodia_fijo = substr($numero, 2, 1).substr($numero, 4, 1);

        $numero = substr($fecha,$pos+53,5);
        $noche_centena = substr($numero, 0, 1);
        $noche_fijo = substr($numero, 2, 1).substr($numero, 4, 1);

        $date = substr($fecha,$pos+71,10);
        $date = strtotime($date);
        $date = date('d/m/Y', $date);
        $noche_fecha = $date;

        $mediodia_fecha = \Carbon\Carbon::createFromFormat("d/m/Y", $mediodia_fecha, 'America/Havana');
        $noche_fecha = \Carbon\Carbon::createFromFormat("d/m/Y", $noche_fecha, 'America/Havana');

        $is_mediodia = true;
        $is_noche = true;

        //Comprobar si la fecha de la tirada es la misma de Hoy
        if($fechaToday->diffInDays($mediodia_fecha) != 0) {
            $mediodia_centena = '-';
            $mediodia_fijo = '-';
            $is_mediodia = false;
        }
        if($fechaToday->diffInDays($noche_fecha) != 0) {
            $noche_centena = '-';
            $noche_fijo = '-';
            $is_noche = false;
        }

        $fechaTodayFinal = "";
        $today_dia_semana = $dias_semana_esp[$fechaToday->dayOfWeek];
        $today_dia = $fechaToday->day;
        $today_mes = $meses_esp[$fechaToday->month - 1];
        $today_anno = $fechaToday->year;

        $fechaTodayFinal = $fechaTodayFinal.$today_dia_semana.", ".$today_dia." de ".$today_mes." de ".$today_anno;

        echo("Fecha Hoy: ".$fechaTodayFinal);
        echo(" </br> ");
        echo(" </br> ");

        if ($today_dia < 10) {
            $today_dia = '0'.$today_dia;
        }
        $fechaDB = $today_anno.'-'.$fechaToday->month.'-'.$today_dia;

        //Enviar datos para el envío del correo con la tirada del día
        $to = ['narvas@nauta.cu'];

        //Actualizar fechas de envío
        if ($is_mediodia) {
            $checkedMediodia = $this->isChecked($fechaDB, 'M');

            echo(" </br> ");
            echo("Mediodía Centena: " . $mediodia_centena);
            echo(" </br> ");
            echo("Mediodía Fijo: " . $mediodia_fijo);
            echo(" </br> ");

            if ($mediodia_centena != '-') {
                echo("Sending email...");
                echo(" </br> ");
                $checkedMediodia = $this->isChecked($fechaDB, 'M');
                if ($checkedMediodia == 0) {
                    $this->insertChecked($fechaDB, 'M');
                    $this->sendEmail($mediodia_centena, $mediodia_fijo, $noche_centena, $noche_fijo, $fechaTodayFinal);
                }
            }
        }
        //Actualizar fechas de envío
        if ($is_noche) {
            $checkedNoche = $this->isChecked($fechaDB, 'N');

            echo(" </br> ");
            echo("Noche Centena: " . $noche_centena);
            echo(" </br> ");
            echo("Noche Fijo: " . $noche_fijo);
            echo(" </br> ");

            if ($noche_centena != '-') {
                echo("Sending email...");
                echo(" </br> ");

                $checkedNoche = $this->isChecked($fechaDB, 'N');
                if ($checkedNoche == 0) {
                    $this->insertChecked($fechaDB, 'N');
                    $this->sendEmail($mediodia_centena, $mediodia_fijo, $noche_centena, $noche_fijo, $fechaTodayFinal);
                }
            }
        }

    }

    public function isChecked($fechaDB, $horario) {
        $query="SELECT * FROM checks WHERE checks.fecha='$fechaDB' AND checks.horario='$horario' LIMIT 1";

        $result = $this->conection->query($query) or die ("Error");
        if ( count($result->fetch_array()) != 0 ) {
            return 1;
        }

        return 0;
    }

    public function insertChecked($fechaDB, $horario) {
        $query="INSERT INTO checks (fecha,horario) VALUES ('".$fechaDB."','".$horario."')";
        $result = $this->conection->query($query) or die ("Error conexion db");
    }

    public function sendEmail($mediodia_centena, $mediodia_fijo, $noche_centena, $noche_fijo, $fechaTodayFinal) {
        // Correo al que queremos que llegue
        $destinatario = "narvas@nauta.cu";
        // Asunto
        $asunto = "NUMERO DEL DIA";
        // Mensaje
        $mensaje =
            "    
            <div class='fecha'>
                <h3>$fechaTodayFinal</h3>
            </div>
        
                <div class='mediodia'>
                    <h3>-MEDIODIA (Centena: $mediodia_centena, Fijo: $mediodia_fijo)</h3>
                </div>              
                
                <div class='noche'>
                    <h3>-NOCHE (Centena: $noche_centena, Fijo: $noche_fijo)</h3>
                </div>        
          ";

        //----- Cabeceras -----
        //----- Para enviar un correo HTML, debe establecerse la cabecera Content-type -----
        $cabeceras  = 'MIME-Version: 1.0' . "\r\n";
        $cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        //----- Cabeceras adicionales -----
        //$cabeceras .= 'To: TU NOMBRE <TU_CORREO_AQUI@gmail.com>' . "\r\n";
        $cabeceras .= 'From: BolitaCubana <bolita@noreply.com>' . "\r\n";

        // Enviamos el email
        if(@mail($destinatario, $asunto, $mensaje, $cabeceras)) {
            echo "El email se envió correctamente a ".$destinatario.".";
        } else {
            echo "El email no se pudo enviar.";
        }
    }



}