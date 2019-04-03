import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { TranslateModule } from '@ngx-translate/core';
import { ProductEditPage } from './product-edit';

@NgModule({
  declarations: [
    ProductEditPage,
  ],
  imports: [
    IonicPageModule.forChild(ProductEditPage),
    TranslateModule.forChild()
  ],
})
export class ProductEditPageModule {}
