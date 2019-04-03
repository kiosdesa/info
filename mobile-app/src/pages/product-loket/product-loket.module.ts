import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';
import { ProductLoketPage } from './product-loket';

@NgModule({
  declarations: [
    ProductLoketPage,
  ],
  imports: [
    IonicPageModule.forChild(ProductLoketPage),
    TranslateModule.forChild()
  ],
})
export class ProductLoketPageModule {}
