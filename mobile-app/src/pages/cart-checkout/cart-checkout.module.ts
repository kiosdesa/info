import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { OfanCoreShareModule } from '../../module/ofancore-share.module';
import { IonicPageModule } from 'ionic-angular';
import { CartCheckoutPage } from './cart-checkout';

@NgModule({
  declarations: [
    CartCheckoutPage
  ],
  imports: [
    IonicPageModule.forChild(CartCheckoutPage),
    TranslateModule.forChild(), 
    OfanCoreShareModule
  ],
})
export class CartCheckoutPageModule {}
