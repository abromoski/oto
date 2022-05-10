     <?php 
                                                       if ($_product->getAttributeText('moment_select') == "Focus"){
                                                          
                                                          echo $this->getViewFileUrl('images/product-bell-curve-focus.png'); 
                                                          
                                                      }
                                                      
                                                      elseif ($_product->getAttributeText('moment_select') == "Amplify"){
                                                         echo $this->getViewFileUrl('images/product-bell-curve-amplify.png');  
                                                          
                                                      }
                                                      
                                                      else {
                                                      echo $this->getViewFileUrl('images/product-bell-curve-balance.png');  
                                                          
                                                      }
                                                          
                                                      ?>