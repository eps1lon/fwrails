<!DOCTYPE html>
<html>
  <head>
    <title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <script src="../javascripts/lib/jquery.min.js"></script>
    <script src="../javascripts/lib/jquery.tablesorter.js"></script>
    <script src="shops.js"></script>
    <style type="text/css">
      .positive {
        background-color: green;
      }
      
      .negative {
        background-color: red;
      }
      
      .neutral {
        background-color: grey;
      }
      
      #shops {
        width: 70%;
      }
      
      #shop_details {
        border: 2px solid dashed;
        margin: 0 0 0 70%;
        position: fixed;
        top: 0px;
      }
      
      #next_best_shops {
          clear: both;
      }
    </style>
  </head>
  <body>
    <table id="shops" class="tablesorter">
      <caption>Lade Shopliste</caption>
    </table>
    <section id="shop_details">
      <h1>Beispielshop</h1>
      <dl>
        <dt>Bester Einkaufspreis: </dt>
        <dd id="best_buyfactor"></dd>
        
        <dt>Bester Verkaufspreis: </dt>
        <dd id="best_sellfactor"></dd>

        <dt>Items: </dt>
        <dd id="items">
          <ul></ul>
        </dd>

        <dt>Nächster Einkaufspreis: </dt>
        <dd id="next_buyfactor"></dd>
        
        <dt>Nächster Verkaufspreis: </dt>
        <dd id="next_sellfactor"></dd>
        
        <dt>Nächstes Angebot: </dt>
        <dd id="next_items"></dd>
      </dl>
    </section>
      
    <h1>Nächster 15er</h1> 
    <ol id="next_best_shops">
      <ul></ul>
    </ol>
  </body>
</html>