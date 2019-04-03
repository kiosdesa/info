import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { OfanCoreShareModule } from '../../module/ofancore-share.module';
import { IonicPageModule } from 'ionic-angular';

import { CartInvoicePage } from './cart-invoice';

@NgModule({
  declarations: [
    CartInvoicePage,
  ],
  imports: [
    IonicPageModule.forChild(CartInvoicePage),
    TranslateModule.forChild(),
    OfanCoreShareModule
  ],
  exports: [
    CartInvoicePage
  ]
})
export class CartInvoicePageModule { }
