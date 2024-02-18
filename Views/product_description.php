<?php $head->css('description.css', true)->css('parameters.css', true);

?>
<p>
<div class="description_box" id="description">
  <div class="parameters_box_text_top_description">Opis produktu <div class="description_box_text_line"></div>
  </div>
</div>


<div class="description_box_product">

  <?= $description['description']; ?>
  </p>


  <div class="parameters_layout">


    <div class="parameters_box">


      <div class="parameters_box_text_top" id="parameters">Dane podstawowe </div>

      <div class="parameter_box_text_line"></div>


    </div>










    <table>
      <?php
      foreach ($parameters as ['key' => $key, 'value' => $value]) :
      ?>
        <tr>
          <td><?= $key ?></td>
          <td><?= $value ?></td>
        </tr>
      <?php endforeach; ?>
    </table>
    <!-- <div class="tab__container">
       <div class="tbody1">
         <table>   <tr> asa </tr>
       <div class="textk">   </div>  </table>
         <table> <tr> asas</tr> </table> </div>
       <div class="tbody">
         <table>   <tr> asa </tr>
       <div class="textk">   </div>  </table>
         <table> <tr> asas</tr> </table> </div>
         <div class="tbody1">
         <table>   <tr> asa </tr>
       <div class="textk">   </div>  </table>
         <table> <tr> asas</tr> </table> </div>
       <div class="tbody">
         <table>   <tr> asa </tr>
       <div class="textk">   </div>  </table>
         <table> <tr> asas</tr> </table> </div>
       <div class="tbody1">
         <table>   <tr> asa </tr>
       <div class="textk">   </div>  </table>
         <table> <tr> asas</tr> </table> </div>
         <div class="tbody">
         <table>   <tr> asa </tr>
       <div class="textk">   </div>  </table>
         <table> <tr> asas</tr> </table> </div>
         <div class="tbody1">
         <table>   <tr> asa </tr>
       <div class="textk">   </div>  </table>
         <table> <tr> asas</tr> </table> </div>
       <div class="tbody2">
         <table>   <tr> asa </tr>
       <div class="textk">   </div>  </table>
         <table> <tr> asas</tr> </table> </div>
         </div> -->






  </div>