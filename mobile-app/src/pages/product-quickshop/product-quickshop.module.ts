import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';
import { ProductQuickshopPage } from './product-quickshop';

@NgModule({
  declarations: [
    ProductQuickshopPage,
  ],
  imports: [
    IonicPageModule.forChild(ProductQuickshopPage),
    TranslateModule.forChild()
  ],
})
export class ProductQuickshopPageModule {}
