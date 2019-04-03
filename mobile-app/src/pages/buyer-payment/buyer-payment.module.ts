import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { BuyerPaymentPage } from './buyer-payment';

@NgModule({
  declarations: [
    BuyerPaymentPage,
  ],
  imports: [
    IonicPageModule.forChild(BuyerPaymentPage),
  ],
})
export class BuyerPaymentPageModule {}
