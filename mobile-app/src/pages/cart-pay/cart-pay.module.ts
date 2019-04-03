import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { OfanCoreShareModule } from '../../module/ofancore-share.module';
import { IonicPageModule } from 'ionic-angular';
import { CartPayPage } from './cart-pay';

@NgModule({
  declarations: [
    CartPayPage,
  ],
  imports: [
    IonicPageModule.forChild(CartPayPage),
    TranslateModule.forChild(),
    OfanCoreShareModule
  ],
})
export class CartPayPageModule {}
