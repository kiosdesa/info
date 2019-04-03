import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { ShippingChoosePage } from './shipping-choose';

@NgModule({
  declarations: [
    ShippingChoosePage,
  ],
  imports: [
    IonicPageModule.forChild(ShippingChoosePage),
    TranslateModule.forChild()
  ],
  exports: [
    ShippingChoosePage
  ]
})
export class ShippingChoosePageModule { }
